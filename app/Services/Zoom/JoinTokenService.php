<?php

namespace App\Services\Zoom;

use App\Models\LiveSession;
use App\Models\User;
use App\Models\ZoomJoinToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JoinTokenService
{
    /**
     * Create join token for user
     */
    public function createToken(User $user, LiveSession $session, Request $request): ZoomJoinToken
    {
        // Check for existing valid token
        $existingToken = ZoomJoinToken::where('user_id', $user->id)
            ->where('live_session_id', $session->id)
            ->valid()
            ->first();

        if ($existingToken) {
            return $existingToken;
        }

        // Generate new token
        $rawToken = Str::random(64);
        $tokenHash = Hash::make($rawToken);

        $expiresAt = now()->addMinutes(config('zoom.token_ttl_minutes', 5));

        // Device/IP binding
        $userAgentHash = null;
        $ipPrefix = null;

        if (config('zoom.security.enable_device_binding', true)) {
            $userAgentHash = hash('sha256', $request->userAgent() ?? '');
        }

        if (config('zoom.security.enable_ip_binding', true)) {
            $ip = $request->ip();
            $ipParts = explode('.', $ip);
            $prefixLength = config('zoom.security.ip_prefix_length', 3);
            $ipPrefix = implode('.', array_slice($ipParts, 0, $prefixLength));
        }

        $token = ZoomJoinToken::create([
            'user_id' => $user->id,
            'live_session_id' => $session->id,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'max_uses' => config('zoom.token_max_uses', 1),
            'user_agent_hash' => $userAgentHash,
            'ip_prefix' => $ipPrefix,
        ]);

        // Store raw token temporarily in session for one-time retrieval
        $request->session()->put("zoom_token_{$token->id}", $rawToken);

        return $token;
    }

    /**
     * Validate token
     */
    public function validateToken(string $token, Request $request): ?ZoomJoinToken
    {
        // Find token by comparing hash
        $tokens = ZoomJoinToken::where('expires_at', '>', now())
            ->where(function ($query) {
                $query->whereNull('used_at')
                      ->orWhereColumn('use_count', '<', 'max_uses');
            })
            ->get();

        foreach ($tokens as $tokenRecord) {
            if (Hash::check($token, $tokenRecord->token_hash)) {
                // Check device/IP binding if enabled
                if (config('zoom.security.enable_device_binding', true) && $tokenRecord->user_agent_hash) {
                    $currentUserAgentHash = hash('sha256', $request->userAgent() ?? '');
                    if ($tokenRecord->user_agent_hash !== $currentUserAgentHash) {
                        continue;
                    }
                }

                if (config('zoom.security.enable_ip_binding', true) && $tokenRecord->ip_prefix) {
                    $currentIp = $request->ip();
                    $ipParts = explode('.', $currentIp);
                    $prefixLength = config('zoom.security.ip_prefix_length', 3);
                    $currentIpPrefix = implode('.', array_slice($ipParts, 0, $prefixLength));
                    if ($tokenRecord->ip_prefix !== $currentIpPrefix) {
                        continue;
                    }
                }

                return $tokenRecord;
            }
        }

        return null;
    }

    /**
     * Revoke token
     */
    public function revokeToken(ZoomJoinToken $token): bool
    {
        $token->update([
            'expires_at' => now()->subMinute(),
        ]);

        return true;
    }

    /**
     * Cleanup expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        return ZoomJoinToken::where('expires_at', '<', now()->subDays(7))
            ->delete();
    }
}


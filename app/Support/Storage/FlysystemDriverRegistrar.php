<?php

namespace App\Support\Storage;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDriveService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Visibility;
use Masbug\Flysystem\GoogleDriveAdapter;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

/**
 * Registers Flysystem drivers that Laravel does not ship with (google, dropbox, azure).
 */
final class FlysystemDriverRegistrar
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        Storage::extend('google', function ($app, array $config) {
            $options = [];
            foreach (['teamDriveId', 'sharedFolderId'] as $key) {
                if (! empty($config[$key] ?? null)) {
                    $options[$key] = $config[$key];
                }
            }
            if (empty($options['sharedFolderId'] ?? null) && ! empty($config['folderId'] ?? null)) {
                $options['sharedFolderId'] = $config['folderId'];
            }

            $client = new GoogleClient;
            $client->setClientId($config['clientId'] ?? '');
            $client->setClientSecret($config['clientSecret'] ?? '');
            $client->refreshToken($config['refreshToken'] ?? '');
            $client->setApplicationName(config('app.name', 'Laravel'));
            $client->setScopes([GoogleDriveService::DRIVE]);

            $service = new GoogleDriveService($client);
            $root = $config['root'] ?? '/';
            $adapter = new GoogleDriveAdapter($service, $root, $options);
            $filesystem = new Flysystem($adapter, ['visibility' => Visibility::PUBLIC]);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });

        Storage::extend('dropbox', function ($app, array $config) {
            $token = $config['authorizationToken'] ?? '';
            $client = new DropboxClient($token);
            $adapter = new DropboxAdapter($client, $config['prefix'] ?? '');
            $filesystem = new Flysystem($adapter, ['visibility' => Visibility::PUBLIC]);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });

        Storage::extend('azure', function ($app, array $config) {
            $accountName = $config['accountName'] ?? '';
            $accountKey = $config['accountKey'] ?? '';
            $endpoint = isset($config['endpoint']) ? rtrim((string) $config['endpoint'], '/') : null;

            if ($endpoint) {
                $connectionString = sprintf(
                    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;BlobEndpoint=%s;',
                    $accountName,
                    $accountKey,
                    $endpoint
                );
            } else {
                $connectionString = sprintf(
                    'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                    $accountName,
                    $accountKey
                );
            }

            $client = BlobRestProxy::createBlobService($connectionString);
            $adapter = new AzureBlobStorageAdapter(
                $client,
                $config['container'] ?? '',
                $config['prefix'] ?? ''
            );
            $filesystem = new Flysystem($adapter, ['visibility' => Visibility::PUBLIC]);

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });

        self::$registered = true;
    }
}

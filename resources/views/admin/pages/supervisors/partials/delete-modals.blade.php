@foreach($supervisors as $supervisor)
    @can('user-delete')
        @include('admin.pages.users.delete', ['user' => $supervisor])
    @endcan
@endforeach

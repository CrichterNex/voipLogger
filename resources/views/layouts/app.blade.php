<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body style="font-family: Tahoma, Nunito, sans-serif">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto m-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="bi bi-circle-fill" id="tcp_listener"></i>  TCP Listener status
                            </a>
                        </li>
                        @if (Auth::user()) 
                        <li class="nav-item">
                            <a class="nav-link" href="{{route('search.index')}}"><i class="bi bi-search"></i>Search</a>
                        </li>
                        @endif
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ ucwords(Auth::user()->name) }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if ($errors->any())
            <div class="alert alert-danger m-0" role="alert">
                @foreach($errors->all() as $error)
                    <li> {{ $error }} </li>
                @endforeach
            </div>
            @endif
            @if (Session::has('success'))

            <div class="alert alert-success m-0" role="alert">
                {{ Session::get('success') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
<script>

function updateTcpListenerStatus() {
    fetch('api/tcp-listener/status')
        .then(response => response.json())
        .then(data => {
            const statusElement = document.getElementById('tcp_listener');
            if (data.status === 'running') {
                if (!statusElement.classList.contains('text-success')) {
                    statusElement.classList.add('text-success');
                }
                if (statusElement.classList.contains('text-danger')) {
                    statusElement.classList.remove('text-danger');
                }
            } else {
                if (!statusElement.classList.contains('text-danger')) {
                    statusElement.classList.add('text-danger');
                }
                if (statusElement.classList.contains('text-success')) {
                    statusElement.classList.remove('text-success');
                }
            }
        })
        .catch(error => console.error('Error fetching TCP listener status:', error));
}
setInterval(updateTcpListenerStatus, 50000); // Update every 5 seconds
updateTcpListenerStatus(); // Initial call to set the status on page load

</script>
</html>

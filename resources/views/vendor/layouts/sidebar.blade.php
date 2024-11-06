<aside class="main-sidebar sidebar-dark-primary bg-navy elevation-4">
    <a href=" {{ route('vendor.dashboard') }} " class="brand-link bg-navy text-center">
        <img src="{{ asset('img/icarry-logo-white.png') }}" alt="iCarry Logo"
            class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light text-yellow float-left">商家後台管理系統</span>
    </a>
    <div class="sidebar">
        <nav id="sidebar" class="mt-2 nav-compact">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ url('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p class="text-sm">首頁</p>
                    </a>
                </li>
                @foreach($mainmenus as $mainmenu)
                    @if($mainmenu->url_type == 1)
                    <li class="nav-item">
                        <a href="{{ url($mainmenu->url) }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                            {!! $mainmenu->fa5icon !!}
                            <p class="text-sm">
                                {{ $mainmenu->name }}
                            </p>
                        </a>
                    </li>
                    @elseif($mainmenu->url_type == 2)
                        @if($mainmenu->code == 'M22S0')
                            @if($vendor->use_sf == 1)
                            <li class="nav-item">
                                <a href="{{ $mainmenu->url }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                                    {!! $mainmenu->fa5icon !!}
                                    <p class="text-sm">
                                        {{ $mainmenu->name }}
                                    </p>
                                </a>
                            </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a href="{{ $mainmenu->url }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                                    {!! $mainmenu->fa5icon !!}
                                    <p class="text-sm">
                                        {{ $mainmenu->name }}
                                        @if($mainmenu->code == 'M20S0')
                                        @if($unconfirmCount > 0)
                                        <span class="right badge badge-warning">{{ $unconfirmCount }}</span>
                                        @endif
                                        @endif
                                    </p>
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item has-treeview">
                            <a href="{{ $mainmenu->url ? $mainmenu->url : 'javascript:' }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                                {!! $mainmenu->fa5icon !!}
                                <p class="text-sm">
                                    {{ $mainmenu->name }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                            @foreach($mainmenu->submenu as $submenu)
                            <li class="nav-item">
                                <a href="{{ $submenu->url ? url($submenu->url) : 'javascript:' }}" class="nav-link" {{ $submenu->open_window ? 'target="_blank"' : '' }}>
                                    {!! $submenu->fa5icon !!}
                                    <p class="text-sm">{{ $submenu->name }}</p>
                                </a>
                            </li>
                            @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
                {{-- 登出 --}}
                <li class="nav-item">
                    <a href="{{ route('vendor.logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-door-open text-danger"></i>
                        <p class="text-sm">登出 (Logout)</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->

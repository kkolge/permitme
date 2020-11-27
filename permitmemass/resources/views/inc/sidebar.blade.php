<nav id="sidebar-nav " >
    @if(!Auth::guest())

    @if(Auth::user()->hasRole(['Super Admin', 'Admin']))
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Roles & Permissions <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/perms">Permissions Details</a>
            <a class="dropdown-item" href="/perms/create">Add Permission</a>
            <a class="dropdown-item" href="/roles">Role Details</a>
            <a class="dropdown-item" href="/roles/create">Add Role</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            User Mgmt <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/usr">System Users</a>
            <a class="dropdown-item" href="/usr/create">Add System User</a>
            <a class="dropdown-item" href="/aur">User Role</a>
            <a class="dropdown-item" href="/aur/create">Link User Role</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Location <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/location">Location Details</a>
            <a class="dropdown-item" href="/location/create">Add Location</a>
            <a class="dropdown-item" href="/linkUser">User Location</a>
            <a class="dropdown-item" href="/linkUser/create">Link User Location</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Device <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/device">Device Details</a>
            <a class="dropdown-item" href="/device/create">Add Device</a>
            <a class="dropdown-item" href="/linkLoc">Device Location</a>
            <a class="dropdown-item" href="/linkLoc/create">Link Device Location</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Admin Reports <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            @if(Auth::user()->hasRole(['Super Admin']))
	            <a class="dropdown-item" href="/reports/allDataReport">All Data</a>
            @endif
            <a class="dropdown-item" href="/adminReports/sReport">Admin Reports</a>
        </div>
    </li>
    @endif
    @if(Auth::user()->hasRole(['Super Admin','Admin','Location User']))
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Registered Users <span class="caret"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/reguser">User List</a>
            <a class="dropdown-item" href="/reguser/create">Add User</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Reports <span class="caret"></span>
        </a>
        
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
	    <a class="dropdown-item" href="/reports/allDataLocationReport">All Data Location</a>
            <a class="dropdown-item" href="/reports/SPO2Report">Low SPO2</a>
            <a class="dropdown-item" href="/reports/TempReport">High Temperature</a>

        </div>
    </li>
    @endif
    @if(Auth::user()->hasRole(['Super Admin','Admin']))

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Hospital <span class="caret"></span>
        </a>
        
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/hospital/dashboard">Dashboard</a>
            <a class="dropdown-item" href="/hospital/beds">Beds</a>
            <a class="dropdown-item" href="/hospital/beds/create">Add Bed</a>
            <a class="dropdown-item" href="/hospital/linkUserBed">Admissions</a>
            <a class="dropdown-item" href="/hospital/linkUserBed/create">Admit Patient</a>
        </div>
    </li>

    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            Restaurants <span class="caret"></span>
        </a>
        
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="/restaurant/dayReport">Day Report</a>
        </div>
    </li>
    @endif
    @endif
</nav>

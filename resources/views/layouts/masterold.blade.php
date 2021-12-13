<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $setting->sitename }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style type="text/css">
      .fa-stack {
        text-align: center;
      }

      .fa-stack .fa-caret-down {
        position: absolute;
        bottom: 0;
      }

      .fa-stack .fa-caret-up {
        position: absolute;
        top: 0;
      }
    </style>
  </head>

  <body class="hold-transition sidebar-mini">
    <div class="wrapper" id="app">
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars" style="color: green;"></i></a>
          </li>

          <li class="nav-item">
            <a class="nav-link" style="color: green !important;" href="#">{{ $setting->sitename }}</a>
          </li>
        </ul>

        <ul class="navbar-nav ml-auto">
        </ul>
      </nav>
      <!-- /.navbar -->

      <!-- Main Sidebar Container -->
      <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ url('/') }}" class="brand-link" style="background: white">
          <img src="{{ asset('img/logo/logo_white.png') }}" alt="AdminLTE Logo" class="brand-image img-circle">
          <span class="brand-text" style="color: black; font-weight: bolder;"> enigm</span><span style="color: #b21a02; font-weight: bolder;">A</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
          <!-- Sidebar user panel (optional) -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
              <h5>{{ Auth::user()->name }}</h5>
              <h6>{{ $my_store->name }}</h6>
            </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            
              <li class="nav-item">
                <router-link to="/home" class="nav-link">
                  <i class="nav-icon fa fa-th"></i>
                  <p>Dashboard</p>
                </router-link>
              </li>

              @if(Auth::user()->role=='Admin')
                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-user"></i>
                        <p>
                          Members
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/admin/customers" class="nav-link ml-3">
                            List of Members
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/members/types" class="nav-link ml-3">
                            Member Type
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/members/sections" class="nav-link ml-3">
                            Member Sections
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/payment/products" class="nav-link ml-3">
                            Payments
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/payment/products" class="nav-link ml-3">
                            Payment Products
                          </router-link>
                        </li>
                        
                        <li class="nav-item">
                          <router-link to="/payment/debits" class="nav-link ml-3">
                            Payment Debits
                          </router-link>
                        </li>
                        
                        <li class="nav-item">
                          <router-link to="/payment/channels" class="nav-link ml-3">
                            Payment Channels
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/payment/banks" class="nav-link ml-3">
                            Payment Banks
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/payment/pos" class="nav-link ml-3">
                            Payment POS
                          </router-link>
                        </li>
                        <li class="nav-item">
                          <router-link to="/admin/customers/fund-history" class="nav-link ml-3">
                            Funding History
                          </router-link>
                        </li>
                        <li class="nav-item">
                          <router-link to="/admin/customers/fund" class="nav-link ml-3">
                            Fund Member's Account
                          </router-link>
                        </li>
                      </ul>
                    </li>

                    <li class="nav-item">
                      <router-link to="/outlets" class="nav-link">
                        <i class="nav-icon fa fa-home"></i>
                        <p>Outlets</p>
                      </router-link>
                    </li>

                    <li class="nav-item">
                      <router-link to="/admin/staff" class="nav-link">
                        <i class="nav-icon fa fa-users"></i>
                        <p>Staff</p>
                      </router-link>
                    </li>

                    @if(Auth::user()->store==1)
                      <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                          <i class="nav-icon fa fa-product-hunt"></i>
                          <p>
                            Warehouse
                            <i class="right fa fa-angle-left"></i>
                          </p>
                        </a>
                        <ul class="nav nav-treeview">
                          <li class="nav-item">
                            <router-link to="/admin/inventory/category" class="nav-link ml-3">
                              Item Category
                            </router-link>
                          </li>

                          <li class="nav-item">
                            <router-link to="/admin/inventory" class="nav-link ml-3">
                              Inventory List
                            </router-link>
                          </li>
                          
                          <li class="nav-item">
                            <router-link to="/admin/purchase/create" class="nav-link ml-3">
                            Create Purchase Order
                            </router-link>
                          </li>

                          <!--<li class="nav-item">
                            <router-link to="/admin/purchases" class="nav-link ml-3">
                            Purchase Orders
                            </router-link>
                          </li>-->

                            <li class="nav-item">
                              <router-link to="/admin/purchases" class="nav-link ml-3">
                                Purchases Orders
                              </router-link>
                            </li>

                            <!--<li class="nav-item">
                                <router-link to="/admin/purchase/create" class="nav-link ml-3">
                                Create Purchase Order
                                </router-link>
                            </li>-->

                            <!--<li class="nav-item">
                                <router-link to="/movements/outletrequest" class="nav-link ml-3">
                                 All Item Request
                                </router-link>
                            </li>

                            <li class="nav-item">
                                <router-link to="/movements/outletmovement" class="nav-link ml-3">
                                 All Item Movement
                                </router-link>
                            </li>--> 
                        </ul>
                      </li>             
                    @endif

                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-product-hunt"></i>
                        <p>
                          {{ $my_store->name }} Store
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/outlets/{{ $my_store->code }}" class="nav-link ml-3">
                            Store
                          </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/movements/myrequest" class="nav-link ml-3">
                             My Item Requests
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/movements/myoutlet" class="nav-link ml-3">
                             My Item Movements
                            </router-link>
                        </li>

                      </ul>
                    </li>

                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-truck"></i>
                        <p>
                          Suppliers
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        

                         <li class="nav-item">
                          <router-link to="/suppliers" class="nav-link ml-3">
                           All Suppliers
                          </router-link>
                        </li>
                        
                        <li class="nav-item">
                          <router-link to="/suppliers/debt" class="nav-link ml-3">
                           Supplier's Debt
                          </router-link>
                        </li>
                      </ul>
                    </li>
                    
                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-shopping-cart"></i>
                        <p>
                          Transactions
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/sale/shopping-cart" class="nav-link ml-3">
                            Generate Invoice
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/quote" class="nav-link ml-3">
                            All Quotes
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/invoice" class="nav-link ml-3">
                            All Invoices
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/orders" class="nav-link ml-3">
                            All Sales
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/debtors" class="nav-link ml-3">
                            Customer Balance Details
                          </router-link>
                        </li>
                      </ul>
                    </li>
                   
                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                          Accounts Management
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/account/type" class="nav-link">
                            <p class="ml-2">Accounts Type</p>
                          </router-link>
                        </li>

                        <!--<li class="nav-item">
                          <router-link to="/account/tax" class="nav-link">
                            <p class="ml-2">Tax Line</p>
                          </router-link>
                        </li>-->

                        <li class="nav-item">
                          <router-link to="/account/chart-of-account" class="nav-link">
                            <p class="ml-2">Chart of Accounts</p>
                          </router-link>
                        </li>

                        <!--<li class="nav-item">
                          <router-link to="/account/journal" class="nav-link">
                            <p class="ml-2">Journal</p>
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/account/ledger" class="nav-link">
                            <p class="ml-2">Ledger</p>
                          </router-link>
                        </li>

                         <li class="nav-item">
                          <router-link to="/account/company-fund" class="nav-link">
                            <p class="ml-2">Inject Capital</p>
                          </router-link>
                        </li>
                       
                        <li class="nav-item">
                          <router-link to="/account/trial-balance" class="nav-link">
                            <p class="ml-2">Trial Balance</p>
                          </router-link>
                        </li>-->

                        <li class="nav-item">
                          <router-link to="/account/reports" class="nav-link">
                           
                            <p class="ml-2">Account Reports</p>
                          </router-link>
                        </li>
                       
                          
                      
                      </ul>
                    </li> 
                
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                          <i class="nav-icon fa fa-cogs"></i>
                          <p>
                            Administration
                            <i class="right fa fa-angle-left"></i>
                          </p>
                        </a>
                        <ul class="nav nav-treeview">
                          @if(Auth::user()->role=='Admin')
                          <li class="nav-item">
                            <router-link to="/admin/setting" class="nav-link ml-3">
                              Site Settings
                            </router-link>
                          </li>
                          @endif
                          <li class="nav-item">
                            <router-link to="/password" class="nav-link ml-3">
                             Change Password
                            </router-link>
                          </li>

                          <!--<li class="nav-item">
                            <router-link to="/pin" class="nav-link ml-3">
                             Change PIN
                            </router-link>
                          </li>-->

                        </ul>
                    </li> 
              @elseif(Auth::user()->role=='Accounting')
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                          <i class="nav-icon fa fa-user"></i>
                          <p>
                            Customers
                            <i class="right fa fa-angle-left"></i>
                          </p>
                        </a>
                        <ul class="nav nav-treeview">
                          <li class="nav-item">
                            <router-link to="/admin/customers" class="nav-link ml-3">
                              List of Customers
                            </router-link>
                          </li>

                     
                          <li class="nav-item">
                            <router-link to="/admin/customers/fund" class="nav-link ml-3">
                              Fund Customer's Account
                            </router-link>
                          </li>

                        </ul>
                    </li>

                    <li class="nav-item">
                      <router-link to="/outlets" class="nav-link">
                        <i class="nav-icon fa fa-home"></i>
                        <p>Outlets</p>
                      </router-link>
                    </li>

                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-product-hunt"></i>
                        <p>
                          Inventory
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/outlets/{{ $my_store->code }}" class="nav-link ml-3">
                            Store
                          </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/movements/myrequest" class="nav-link ml-3">
                             My Item Requests
                            </router-link>
                        </li>

                        <li class="nav-item">
                            <router-link to="/movements/myoutlet" class="nav-link ml-3">
                             My Item Movements
                            </router-link>
                        </li>
                        
                      </ul>
                    </li>
                    
                    
                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-shopping-cart"></i>
                        <p>
                          Transactions
                          <i class="right fa fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/sale/shopping-cart" class="nav-link ml-3">
                            Generate Invoice
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/invoice" class="nav-link ml-3">
                            All Invoices
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/quote" class="nav-link ml-3">
                            All Quotes
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/sale/orders" class="nav-link ml-3">
                            All Sales
                          </router-link>
                        </li>
                      </ul>
                    </li>
                   
                    <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-book"></i>
                        <p>
                          Accounts Management
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <router-link to="/account/type" class="nav-link">
                            <p class="ml-2">Accounts Type</p>
                          </router-link>
                        </li>

                        <!--<li class="nav-item">
                          <router-link to="/account/tax" class="nav-link">
                            <p class="ml-2">Tax Line</p>
                          </router-link>
                        </li>-->

                        <li class="nav-item">
                          <router-link to="/account/chart-of-account" class="nav-link">
                            <p class="ml-2">Chart of Accounts</p>
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/account/journal" class="nav-link">
                            <p class="ml-2">Journal/Transactions</p>
                          </router-link>
                        </li>

                        <li class="nav-item">
                          <router-link to="/account/ledger" class="nav-link">
                            <p class="ml-2">Ledger</p>
                          </router-link>
                        </li>

                         <!--<li class="nav-item">
                          <router-link to="/account/company-fund" class="nav-link">
                            <p class="ml-2">Inject Capital</p>
                          </router-link>
                        </li>
                       
                        <li class="nav-item">
                          <router-link to="/account/trial-balance" class="nav-link">
                            <p class="ml-2">Trial Balance</p>
                          </router-link>
                        </li>-->

                        <li class="nav-item">
                          <router-link to="/account/reports" class="nav-link">
                           
                            <p class="ml-2">Reports</p>
                          </router-link>
                        </li>
                       
                          
                      
                      </ul>
                    </li> 
                
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                          <i class="nav-icon fa fa-cogs"></i>
                          <p>
                            Administration
                            <i class="right fa fa-angle-left"></i>
                          </p>
                        </a>
                        <ul class="nav nav-treeview">
                          @if(Auth::user()->role=='Admin')
                          <li class="nav-item">
                            <router-link to="/admin/setting" class="nav-link ml-3">
                              Site Settings
                            </router-link>
                          </li>
                          @endif
                          <li class="nav-item">
                            <router-link to="/password" class="nav-link ml-3">
                             Change Password
                            </router-link>
                          </li>

                          <!--<li class="nav-item">
                            <router-link to="/pin" class="nav-link ml-3">
                             Change PIN
                            </router-link>
                          </li>-->

                        </ul>
                    </li> 
              @elseif(Auth::user()->role=='Marketing')
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon fa fa-user"></i>
                      <p>
                        Customers
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <li class="nav-item">
                        <router-link to="/admin/customers" class="nav-link ml-3">
                          List of Customers
                        </router-link>
                      </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fa fa-product-hunt"></i>
                    <p>
                      Inventory
                      <i class="right fa fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <router-link to="/outlets/{{ $my_store->code }}" class="nav-link ml-3">
                        Store
                      </router-link>
                    </li>


                    
                  </ul>
                </li>
                
                <li class="nav-item has-treeview">
                  <a href="#" class="nav-link">
                    <i class="nav-icon fa fa-shopping-cart"></i>
                    <p>
                      Transactions
                      <i class="right fa fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <router-link to="/sale/shopping-cart" class="nav-link ml-3">
                        Generate Invoice
                      </router-link>
                    </li>

                    <li class="nav-item">
                      <router-link to="/sale/invoice" class="nav-link ml-3">
                        All Invoices
                      </router-link>
                    </li>

                    <li class="nav-item">
                      <router-link to="/sale/quote" class="nav-link ml-3">
                        All Quotes
                      </router-link>
                    </li>

                    <li class="nav-item">
                      <router-link to="/sale/orders" class="nav-link ml-3">
                        All Sales
                      </router-link>
                    </li>
                  </ul>
                </li>
            
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon fa fa-cogs"></i>
                      <p>
                        Administration
                        <i class="right fa fa-angle-left"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      @if(Auth::user()->role=='Admin')
                      <li class="nav-item">
                        <router-link to="/admin/setting" class="nav-link ml-3">
                          Site Settings
                        </router-link>
                      </li>
                      @endif
                      <li class="nav-item">
                        <router-link to="/password" class="nav-link ml-3">
                         Change Password
                        </router-link>
                      </li>

                      <!--<li class="nav-item">
                        <router-link to="/pin" class="nav-link ml-3">
                         Change PIN
                        </router-link>
                      </li>-->

                    </ul>
                </li> 
              @endif


              <li class="nav-item">
                <a class="nav-link" href="{{ route('logout') }}"
                  onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();">
                  <i class="nav-icon fa fa-power-off text-red"></i>
                  <p>{{ __('Logout') }}</p>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
              </li>
            </ul>
          </nav>

        </div>
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <router-view></router-view>
        <vue-progress-bar></vue-progress-bar>
      </div>
 
      <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="{{ url('/') }}">{{ $setting->sitename }}</a>.</strong> 
      </footer>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <style type="text/css">
      body{
        font-size: 13px !important;
      }
    </style>
  </body>
</html>

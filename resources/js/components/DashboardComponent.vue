<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid mb-2">
            <div class="pt-5" v-if="user.role==8">
                <div v-if="user.store=='---'" class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                     
                                <form @submit.prevent="loginUser()">
                                    <b-form-group label="Select Bar:">
                                        <v-select label="name" :options="stores" @input="setSelected" ></v-select>
                                    </b-form-group>
                                    <button v-show="!editMode" type="submit" class="btn btn-primary btn-lg btn-block">
                                        Enter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <div class="card">
                        <div class="card-header">
                            <h3> Bar Inventory
                                <b-button variant="outline-primary" size="sm" @click="logout" class="pull-right mb-2">
                                    Bar Sign-out
                                </b-button>
                            </h3>
                        </div>

                        <div class="card-body table-responsive p-0" v-if="invt.data.length>0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="300px">
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Name</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByName()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>

                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Category</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByCategory()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Amount (<span v-html="nairaSign"></span>)</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByAmount()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Quantity</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByQuantity()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <!--<th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Threshold</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByThreshold()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Age Period</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByPeriod()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th v-if="unprintable==false">Action</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr  v-for="(inventory, index) in invt.data">
                                        <td>{{ inventory.product_name }}</td>
                                        <td>{{ inventory.name }}</td>
                                        <td>{{ formatPrice(inventory.price) }}</td>
                                        <td>
                                            <span class="text-red" v-if="inventory.threshold > inventory.quantity">{{ inventory.quantity }}</span>
                                            <span v-else>{{ inventory.quantity }}</span>
                                        </td>
                                        <!--<td>{{ inventory.threshold }}</td>
                                        <td>{{ inventory.updated_at }}</td>-->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body" v-else>
                            <div class="alert alert-info text-center"><h3><strong>No Item Found.</strong></h3></div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-2">
                                    <br> Total: <b>{{ all_inventory }} Items</b>
                                </div>
                                <pagination :data="invt" @pagination-change-page="getResults"></pagination>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-5" v-else-if="user.role==7">
                <div v-if="login" class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <tr>
                                  <th>Login Time</th>
                                  <td>{{ login.created_at }}</td>
                                </tr>
                                
                                <tr>
                                  <th>Logout Time</th>
                                  <td>{{ login.logout }} <a href="javascript:void(0)" @click="returnLogin()">Return Back</a></td>
                                </tr>

                                <tr>
                                  <th>Verified?</th>
                                  <td>Not Verified, contact the store manager to verify and approve your logout</td>
                                </tr>

                                <tr>
                                  <th>Bar Accessed</th>
                                  <td>{{ store.name }} 
                                    <a href="javascript:void(0)" @click="pview()">View Products</a>
                                    </td>
                                </tr>                   
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else-if="user.store=='---'" class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                     
                                <form @submit.prevent="loginUser()">
                                    <b-form-group label="Select Bar:">
                                        <v-select label="name" :options="stores" @input="setSelected" ></v-select>
                                    </b-form-group>
                                    <button v-show="!editMode" type="submit" class="btn btn-primary btn-lg btn-block">
                                        Enter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <div class="card">
                        <div class="card-header">
                            <h3> Bar Inventory
                                <b-button variant="outline-primary" size="sm" @click="logout" class="pull-right mb-2">
                                    Bar Sign-out
                                </b-button>
                            </h3>
                        </div>

                        <div class="card-body table-responsive p-0" v-if="invt.data.length>0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="300px">
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Name</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByName()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>

                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Category</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByCategory()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Amount (<span v-html="nairaSign"></span>)</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByAmount()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Quantity</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByQuantity()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <!--<th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Threshold</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByThreshold()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th>
                                            <div class="float-left">
                                                <span style="padding-right: 8px">Age Period</span>
                                                <a href="javascript:void(0)" class="fa fa-stack" @click="orderByPeriod()">
                                                    <i class="fa fa-caret-up" aria-hidden="true"></i>
                                                    <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </th>
                                        <th v-if="unprintable==false">Action</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr  v-for="(inventory, index) in invt.data">
                                        <td>{{ inventory.product_name }}</td>
                                        <td>{{ inventory.name }}</td>
                                        <td>{{ formatPrice(inventory.price) }}</td>
                                        <td>
                                            <span class="text-red" v-if="inventory.threshold > inventory.quantity">{{ inventory.quantity }}</span>
                                            <span v-else>{{ inventory.quantity }}</span>
                                        </td>
                                        <!--<td>{{ inventory.threshold }}</td>
                                        <td>{{ inventory.updated_at }}</td>-->
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-body" v-else>
                            <div class="alert alert-info text-center"><h3><strong>No Item Found.</strong></h3></div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-2">
                                    <br> Total: <b>{{ all_inventory }} Items</b>
                                </div>
                                <pagination :data="invt" @pagination-change-page="getResults"></pagination>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row pt-5" v-else-if="user.role==6">
                <div class="col-lg-6">
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h5>{{ stat.inventories }}</h5>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>{{ stat.customers }}</h5>

                            <p>Registered Members</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        
                    </div>
                </div>

               

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Latest Quotes</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                      
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in quotes.data" :key="order.id">
                                            <td><a href="javascript:void(0)" @click="viewItems(order)">{{ order.sale_id }}</a></td>
                                            <td>{{ order.user_name }}</td>
                                            <td><span class="badge badge-warning">{{ order.status | capitalize }}</span></td>
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.created_at | myDate }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                        <div class="card-footer clearfix">
                            <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right" @click="$router.push({name: 'Quotes'})">View All Quotes</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Members</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
 
                        <div class="card-body p-0">
                            <ul class="users-list clearfix">
                                <li v-for="user in customers.data" :key="user.id">
                                    <img :src="'/img/avatar.png'">
                                    <a class="users-list-name" href="javascript:void(0)" @click="view(user)">{{ user.name }}</a>
                                    <span class="users-list-date">{{ user.created_at | myDate }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Customers'})" >View All Members</a>
                        </div>
                    </div>
                </div>
      
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Latest Invoices</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                      
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in invoices.data" :key="order.id">
                                            <td><a href="javascript:void(0)" @click="viewItems(order)">{{ order.sale_id }}</a></td>
                                            <td>{{ order.user_name }}</td>
                                            <td><span class="badge badge-info">{{ order.status | capitalize }}</span></td>
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.created_at | myDate }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                        <div class="card-footer clearfix">
                            <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right" @click="$router.push({name: 'Quotes'})">View All Quotes</a>
                        </div>
                    </div>

                    

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <li v-for="product in inventories.data" :key="product.id" class="item">
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">
                                            {{ product.product_name }}
                                            <span class="badge badge-warning float-right">
                                                <span v-html="nairaSign"></span>
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </a>
                                        <span class="product-description">
                                            {{ product.name }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                 
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Inventory'})" class="uppercase">View All Products</a>
                        </div>
                    </div>   
                </div>
            </div>

            <div class="row pt-5" v-else-if="user.role==5">
                <div class="col-lg-6">
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h5>{{ stat.inventories }}</h5>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>{{ stat.customers }}</h5>

                            <p>Registered Members</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        
                    </div>
                </div>

               

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Members</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
 
                        <div class="card-body p-0">
                            <ul class="users-list clearfix">
                                <li v-for="user in customers.data" :key="user.id">
                                    <img :src="'/img/avatar.png'">
                                    <a class="users-list-name" href="javascript:void(0)" @click="view(user)">{{ user.name }}</a>
                                    <span class="users-list-date">{{ user.created_at | myDate }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Customers'})" >View All Members</a>
                        </div>
                    </div>
                </div>
      
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <li v-for="product in inventories.data" :key="product.id" class="item">
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">
                                            {{ product.product_name }}
                                            <span class="badge badge-warning float-right">
                                                <span v-html="nairaSign"></span>
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </a>
                                        <span class="product-description">
                                            {{ product.name }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                 
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Inventory'})" class="uppercase">View All Products</a>
                        </div>
                    </div>   
                </div>
            </div>

            <div class="row pt-5" v-else-if="user.role==3">
                <div class="col-lg-6">
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h5>{{ stat.inventories }}</h5>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>{{ stat.customers }}</h5>

                            <p>Registered Members</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        
                    </div>
                </div>

               

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Members</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
 
                        <div class="card-body p-0">
                            <ul class="users-list clearfix">
                                <li v-for="user in customers.data" :key="user.id">
                                    <img :src="'/img/avatar.png'">
                                    <a class="users-list-name" href="javascript:void(0)" @click="view(user)">{{ user.name }}</a>
                                    <span class="users-list-date">{{ user.created_at | myDate }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Customers'})" >View All Members</a>
                        </div>
                    </div>
                </div>
      
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <li v-for="product in inventories.data" :key="product.id" class="item">
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">
                                            {{ product.product_name }}
                                            <span class="badge badge-warning float-right">
                                                <span v-html="nairaSign"></span>
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </a>
                                        <span class="product-description">
                                            {{ product.name }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                 
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Inventory'})" class="uppercase">View All Products</a>
                        </div>
                    </div>   
                </div>
            </div>

            <div class="row pt-5" v-else-if="user.role==2">
                <div class="col-lg-6">
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h5>{{ stat.inventories }}</h5>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>{{ stat.customers }}</h5>

                            <p>Registered Members</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        
                    </div>
                </div>

               

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Members</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
 
                        <div class="card-body p-0">
                            <ul class="users-list clearfix">
                                <li v-for="user in customers.data" :key="user.id">
                                    <img :src="'/img/avatar.png'">
                                    <a class="users-list-name" href="javascript:void(0)" @click="view(user)">{{ user.name }}</a>
                                    <span class="users-list-date">{{ user.created_at | myDate }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Customers'})" >View All Members</a>
                        </div>
                    </div>
                </div>
      
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <li v-for="product in inventories.data" :key="product.id" class="item">
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">
                                            {{ product.product_name }}
                                            <span class="badge badge-warning float-right">
                                                <span v-html="nairaSign"></span>
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </a>
                                        <span class="product-description">
                                            {{ product.name }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                 
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Inventory'})" class="uppercase">View All Products</a>
                        </div>
                    </div>   
                </div>
            </div>



            <div class="row pt-5" v-else>
                <div class="col-lg-6">
                    <div class="small-box bg-blue">
                        <div class="inner">
                            <h5>{{ stat.inventories }}</h5>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h5>{{ stat.customers }}</h5>

                            <p>Registered Members</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user-plus"></i>
                        </div>
                        
                    </div>
                </div>

               

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Latest Quotes</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                      
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in quotes.data" :key="order.id">
                                            <td><a href="javascript:void(0)" @click="viewItems(order)">{{ order.sale_id }}</a></td>
                                            <td>{{ order.user_name }}</td>
                                            <td><span class="badge badge-warning">{{ order.status | capitalize }}</span></td>
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.created_at | myDate }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                        <div class="card-footer clearfix">
                            <a href="javascript:void(0)" class="btn btn-sm btn-info float-left" @click="$router.push({name: 'sell'})">Place New Order</a>

                            <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right" @click="$router.push({name: 'Quotes'})">View All Quotes</a>
                        </div>
                    </div>

                    <!--<div class="card direct-chat direct-chat-warning">
                        <div class="card-header">
                            <h3 class="card-title">Notifications</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body">
                            <div class="direct-chat-messages">
                                <div class="direct-chat-msg" v-if="Number(stat.fund) > Number(0)">
                                    You have pending fund transaction
                                    <a href="javascript:void(0)" @click="$router.push({name: 'wallet-funding'})" class="pull-right">View</a>
                                    <hr>
                                </div>

                                <div class="direct-chat-msg" v-if="Number(stat.approve_purchase) > Number(0)">
                                    You have pending product purchases needed to be approved
                                    <a href="javascript:void(0)" @click="$router.push({name: 'allpurchases'})" class="pull-right">View</a>
                                    <hr>
                                </div>

                                <div class="direct-chat-msg" v-if="Number(stat.accept_purchase) > Number(0)">
                                    You have products purchased needed to be accepted into your outlet
                                    <a href="javascript:void(0)" @click="$router.push({name: 'purchases'})" class="pull-right">View</a>
                                    <hr>
                                </div>

                                <div class="direct-chat-msg" v-if="Number(stat.moved) > Number(0)">
                                    You have some products awaiting your approval to be moved to other outlets
                                    <a href="javascript:void(0)" @click="$router.push({name: 'mymovement'})" class="pull-right">View</a>
                                    <hr>
                                </div>

                                <div class="direct-chat-msg" v-if="(Number(stat.requested1) > Number(0)) || (Number(stat.requested2) > Number(0))">
                                    You have some products awaiting your acceptance into your outlet
                                    <a href="javascript:void(0)" @click="$router.push({name: 'myrequest'})" class="pull-right">View</a>
                                    <hr>
                                </div>

                            </div>
                        </div>
                    </div>-->

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Latest Members</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
 
                        <div class="card-body p-0">
                            <ul class="users-list clearfix">
                                <li v-for="user in customers.data" :key="user.id">
                                    <img :src="'/img/avatar.png'">
                                    <a class="users-list-name" href="javascript:void(0)" @click="view(user)">{{ user.name }}</a>
                                    <span class="users-list-date">{{ user.created_at | myDate }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Customers'})" >View All Members</a>
                        </div>
                    </div>
                </div>
      
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Latest Invoices</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                      
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="order in invoices.data" :key="order.id">
                                            <td><a href="javascript:void(0)" @click="viewItems(order)">{{ order.sale_id }}</a></td>
                                            <td>{{ order.user_name }}</td>
                                            <td><span class="badge badge-info">{{ order.status | capitalize }}</span></td>
                                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                                            <td>{{ order.created_at | myDate }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    
                        <div class="card-footer clearfix">
                            <a href="javascript:void(0)" class="btn btn-sm btn-info float-left" @click="$router.push({name: 'sell'})">Place New Order</a>

                            <a href="javascript:void(0)" class="btn btn-sm btn-secondary float-right" @click="$router.push({name: 'Quotes'})">View All Quotes</a>
                        </div>
                    </div>

                    

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recently Added Products</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                     
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <li v-for="product in inventories.data" :key="product.id" class="item">
                                    <div class="product-info">
                                        <a href="javascript:void(0)" class="product-title">
                                            {{ product.product_name }}
                                            <span class="badge badge-warning float-right">
                                                <span v-html="nairaSign"></span>
                                                {{ formatPrice(product.price) }}
                                            </span>
                                        </a>
                                        <span class="product-description">
                                            {{ product.name }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                 
                        <div class="card-footer text-center">
                            <a href="javascript:void(0)" @click="$router.push({name: 'Inventory'})" class="uppercase">View All Products</a>
                        </div>
                    </div>   
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from "moment";

    export default {
        created() {
            this.getUser();
            this.getStat();
            this.loadQuote();
            this.loadSales();
            this.loadUsers();
            this.loadInventory();
        },

        data() {
            return {
                is_busy: false,
                user: "",
                editMode: false,
                stat: '',
                nairaSign: "&#x20A6;",
                filterForm: {
                    start_date: '',
                    end_date: '',
                    customer: '',
                    selected: 5,
                },
                all_inventory: '',
                invoices: {},
                invoices: {
                    data: '',
                },
                customers: [],
                inventories: [],
                invt: {},
                invt: {
                    data: {},
                },
                quotes: {},
                quotes: {
                    data: '',
                },
                stores: [],
                login: '',
                store: '',
                form: new Form({
                    id: "",
                    user_id: "",
                    bar: '',
                }),
            };
        },

        methods: {
            getUser() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.stores = data.stores;
                    this.store = data.store;
                    this.login = data.login;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            loadUsers() {
                this.filterForm.selected = 8;
                axios.get("/api/customer", { params: this.filterForm })
                .then(({ data }) => {
                    this.customers = data.customers;
                });
            },

            getStat() {
                axios.get("/api/stat")
                .then(({ data }) => {
                    this.stat = data;
                });
            },

            setSelected(value) {
                this.form.bar = value.id;
                this.form.user_id = this.user.id;
            },

            loginUser() {
               
                if(this.form.bar=='')
                {
                    Swal.fire(
                        "Failed!",
                        "Ops, Please Select Bar.",
                        "warning"
                    );
                }
                else {
                    this.form.post("/api/user/login")
                    .then(() => {
                        Swal.fire(
                            "Created!",
                            "Logged in Successfully.",
                            "success"
                        );
                        
                        location.reload();
                    })

                    .catch();
                }
            },

            logout() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/user/login")
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Logout Successfully.",
                        "success"
                    );
                    
                    location.reload();
                })

                .catch()
                .finally(() => {
                    this.is_busy = false;
                });
            },

            Login() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/user/relogin/'+this.login.id)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Returned Back Successfully.",
                        "success"
                    );
                    
                    location.reload();
                })

                .catch()
                .finally(() => {
                    this.is_busy = false;
                });
            },

            view(user) {
                this.$router.push({ path: "/admin/customers/" + user.unique_id });
            },

            pview() {
                this.$router.push({ path: "/outlets/" + this.store.code });
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            loadSales() {
                axios.get('/api/store/invoice', { params: this.filterForm })
                .then((data) => {
                    if(this.filterForm.selected==0)
                    {
                        this.invoices.data = data.data.report_data;
                    }
                    else{
                        this.invoices = data.data.report_data;
                    }             
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

            loadQuote() {
                axios.get('/api/store/quotes', { params: this.filterForm })
                .then((data) => {
                    if(this.filterForm.selected==0)
                    {
                        this.quotes.data = data.data.report_data;
                    }
                    else{
                        this.quotes = data.data.report_data;
                    }
                    
                })

                .catch((err) => {
                    console.log(err);
                });
            },

            viewItems(order) {
                this.$router.push({ path: "/orderview/" + order.sale_id });
            },

            loadInventory() {
                axios.get("/api/inventory", { params: this.filterForm })
                .then(({ data }) => {
                    this.inventories = data.inventories;
                    this.invt = data.invt;
                    this.all_inventory = data.all_inventory;
                });
            },

            getResults(page = 1) {
                axios.get("/api/inventory/" + "?page=", { params: this.filterForm })
                .then(response => {
                    this.inventories = response.data.inventories;
                    this.invt = response.data.invt;
                });
            }, 
        }      
    };
</script>
<style>
    .help-img{
        height:40px;
        width:40px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .hover:hover {
        background-color:#ECF0F5;
    }

    .routerText{
        font-size:15px
    }

    .margin-bottom {
        margin-bottom: 50px
    }
    .text-center{
        padding: 10px
    }
</style>
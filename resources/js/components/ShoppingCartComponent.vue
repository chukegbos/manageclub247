<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-7">
                    <h2><strong>Create Quote</strong></h2>
                </div>
                <div class="col-md-5">
                    <b-button type="button" variant="primary" @click="onAddNewProduct" size="sm" class="float-right m-2">
                        Add Item
                    </b-button>
                    <!--<b-button type="button" variant="primary" @click="onAddNewService" size="sm" class="float-right m-2">
                        Add Service
                    </b-button>-->
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <b-form @submit.stop.prevent="is_edit ? updateForm() : onFormSubmit()">
                        <h4 class="p-2"><b>Item List</b></h4>
                        <table class="table table-striped table-responsive-md text-center">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="100px">Quantity</th>
                                    <th>Available</th>
                                    <th>Unit Price (<span v-html="nairaSign"></span>)</th>
                                    <th>Total Price (<span v-html="nairaSign"></span>)</th>
                                    <!--<th width="150px">Discount per Item (%)</th>-->
                                    <th>
                                        
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in form.productItems">
                                    <td>
                                        <!--<vue-typeahead-bootstrap
                                          v-model="item.product_name"
                                          :ieCloseFix="false"
                                          :data="products"
                                          :serializer="data => data.product_name"
                                          @hit="addProductItem($event, index)"
                                          placeholder="Search for product"
                                          @input="lookup(item)"
                                        />-->

                                        <span v-if="item.product_name">{{ item.product_name }}</span>
                                      
                                        <v-select label="product_name" :options="products" @input="setProduct($event, item)" v-else></v-select>

                                    </td>
                                    <td>
                                        
                                        <b-form-input v-model="item.qty" max="item.number" type="number" class="form-control qty-input"  @input="updateText(item)"></b-form-input>
                                      
                                    </td>
                                    <td>
                                        {{ item.number }}
                                    </td>
                                    <td>{{ item.price }}</td>
                                    <td>
                                        {{ (item.qty * item.price) - ((item.discount/100)*(item.qty * item.price))}}
                                    </td>

                                    <!--<b-form-input v-model="item.discount" type="number" class="form-control qty-input" @input="updateDiscount(item)"></b-form-input>-->

                                    <td>
                                        <a href="javascript:void(0)" @click="onRemoveProduct(form.productItems.indexOf(item))"><i class="fa fa-times text-red 2x"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!--<h4 class="p-2"><b>Service List</b></h4>

                        <table class="table table-striped table-responsive-md text-center">
                            <thead>
                                <tr>
                                    <th width="600px">Service</th>
                                    <th>Price (<span v-html="nairaSign"></span>)</th>
                                    <th>
                                        
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(service, index) in form.serviceItems">
                                    <td>
                                        <b-form-input v-model="service.title" type="text" class="form-control" placeholder="installation of equipment"></b-form-input>
                                    </td>
                                    <td>
                                        <b-form-input v-model="service.price"  type="number" class="form-control qty-input"  @input="getTotal()"></b-form-input>
                                    </td>

                                    <td>
                                        <a href="javascript:void(0)" @click="onRemoveService(form.serviceItems.indexOf(service))"><i class="fa fa-times text-red 2x"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>-->

                        <table class="table table-bordered table-responsive-md text-center">
                            <thead>
                                <tr>
                                    <th>User Type</th>
                                    <th>Name</th>
                                    <th>Type of sale</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <select v-model="form.user_type" class="form-control">
                                            <option v-for="type in user_type" v-bind:value="type.value">
                                                {{ type.text }}
                                            </option>
                                        </select>
                                    </th>

                                    <th>
                                        <span v-if="form.user_type==1">
                                            <v-select label="name" :options="users" @input="getUserID($event)"></v-select>
                                        
                                            <b-button variant="info" size="sm" class="float-right mt-1 mb-1" v-on:click="CreateCustomer()">New Customer</b-button>
                                        </span>
                                        <span v-else>
                                            <b-form-input v-model="form.guest"  type="text" class="form-control" placeholder="Name of guest (Optional)"></b-form-input>
                                        </span>
                                    </th>
                                    <th>
                                        <select v-model="form.mop" class="form-control">
                                            <option v-for="option in options" v-bind:value="option.value">
                                                {{ option.text }}
                                            </option>
                                        </select>
                                    </th>
                                    <th><h4><b><span v-html="nairaSign"></span>{{ formatPrice(form.amount) }}</b></h4></th>
                                </tr>
                            </tbody>
                        </table>

                        <b-button type="submit" variant="primary">Submit</b-button>
                    </b-form>
                </div>
            </div>

            <b-modal id="form-modal" ref="formCustomer" :title="'New Customer'" hide-footer>
                <b-form @submit.stop.prevent="onCustomerSubmit($event)">
                    <fieldset>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Customer Name</label>
                                <input v-model="formCustomer.name" type="text" class="form-control"/>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Contact Person</label>
                                <input v-model="formCustomer.c_person" type="text" class="form-control"/>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Email Address</label>
                                <input v-model="formCustomer.email" type="email" class="form-control"/>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Phone Number</label>
                                <input v-model="formCustomer.phone" type="tel" class="form-control">
                            </div>

                            <div class="col-md-12 form-group">
                                <label>Address</label>
                                <textarea v-model="formCustomer.address" class="form-control"></textarea>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>City</label>
                                <input v-model="formCustomer.city" type="text" class="form-control"/>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>State</label>
                                <input v-model="formCustomer.state" type="text" class="form-control"/>
                            </div>
                        </div>
                        <b-button type="submit" variant="primary">Save</b-button>
                    </fieldset>
                </b-form>
            </b-modal>

            <div class="modal fade" id="discountModal" tabindex="-1" role="dialog" aria-labelledby="addNewstoreLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Discount</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.stop.prevent="onApply">
                            <div class="modal-body">
                                <div class="form-group">
                                    <input v-model="discount" type="number" class="form-control"  :max="user.sale_percent"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Apply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import axios from 'axios';
    import {debounce} from 'lodash';

    export default {
        created(){
            this.onAddProduct();
            this.getUser();
        },

        data(){
            return{
                user: '',
                product: null,
                products: [],
                nairaSign: "&#x20A6;",
                typeQuery: '',
             
                sale_id: '',
                form: new Form({
                    user: '',
                    user_id: '',
                    productItems: [],
                    serviceItems: [],
                    amount: 0,
                    mop: 1,
                    user_type: '',
                    guest: '',
                }),

                applied_discount: {
                    percent: '',
                },

                fil: {
                    store: '',
                    inventory: '',
                },

                is_edit: false,
                edit_model: null,
                is_busy: false,
                user_store: '',
                userQuery: '',
                users: [],
                formCustomer: new Form({
                    id: "",
                    name: "",
                    email: "",
                    phone: "",
                    address: "",
                    state: "",
                    credit_unit: "",
                    city: "",
                    c_person: "",
                }),
                discount: 0,
                options: [
                    { text: 'Cash Sale', value: '1' },
                    { text: 'Credit Sale', value: '0' },
                ],

                user_type: [
                    { text: 'Member', value: 1 },
                    { text: 'Guest', value: 0 },
                ],

                sale: '',
                errors: [],
            }
        },

        methods: {
            getUser() {
                if(this.is_busy) return;
                this.is_busy = true;
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.user_store = data.user_store;
                    this.discount = data.user.sale_percent;
                    this.store = data.store;
                    this.products = data.inventory;
                    this.stores = data.stores;
                    this.suppliers = data.suppliers;
                    this.accounts = data.accounts;
                    this.users = data.customers;
                })
                .catch(error => {
                    Swal.fire(
                        "Failed!",
                        "There is error somewhere",
                        "error"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            /*lookup(item){
                debounce(() => {
                    fetch('/api/store/loadinventory', {params: item.product_name})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.products = data;
                    })
                }, 500)();
            },*/

            lookUser(){
                debounce(() => {

                    fetch('/api/searchcustomer', {params: this.userQuery})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.users = data;
                    })
                }, 500)();
            },

            onFormSubmit($event){
                if (this.is_busy) return;
                this.is_busy = true;

                axios.post('/api/cart/checkout', this.form)
                .then((data)=>{
                    Swal.fire(
                        'Created!',
                        'Quote Created Successfully.',
                        'success'
                    )
                    this.$router.push({ path: '/orderview/' + data.data});
                })

                .catch((error) => {
                })
                .finally(() => {
                this.is_busy = false;
            });
            },

            updateForm($event) {
                if (this.is_busy) return;
                this.is_busy = true;
             
                axios.put('/api/cart/checkout/' + this.sale.id, this.form)
                .then((data)=>{
                    Swal.fire(
                        'Created!',
                        'Quote Updated Successfully.',
                        'success'
                    )
                    this.$router.push({ path: '/orderview/' + data.data});
                })

                .catch((error) => {
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onAddProduct(){
                this.sale_id = this.$route.params.sale_id;

                if(this.sale_id){
                    axios.get("/api/cart/getorder/" + this.sale_id)

                    .then((response) => {
                        if(response.data.error){
                            axios.get('/api/customer/' + this.sale_id)
                            .then((data) => {

                                if(data.data.error){
                                    Swal.fire(
                                        "Failed!",
                                        'Nothing found',
                                        "warning"
                                    );
                                    this.$router.push({ path: "/admin/customers"});
                                }
                                else
                                {
                                    this.form.user_id = data.data.user.id;
                                    this.userQuery = data.data.user.name;
                                    debounce(() => {

                                        fetch('/api/searchcustomer', {params: this.userQuery})
                                        .then(response => {
                                            return response.json();
                                        })
                                        .then(data => {
                                            this.users = data;
                                        })
                                    }, 500)();

                                    this.form.productItems.push(this.setItemModel({}));
                                }
                            })
                        }
                        else{
                            
                            this.is_edit = true;
                            this.form.productItems = response.data.items;
                            this.form.serviceItems = response.data.services;
                            this.form.amount = response.data.sale.totalPrice;
                            this.form.mop = response.data.sale.mop;

                            this.form.guest = response.data.sale.guest;
                            this.form.user_type = response.data.sale.user_type;
                            this.sale = response.data.sale;
                            this.form.user_id = response.data.customer.id;
                            this.userQuery = response.data.customer.name;
                        }
                    })

                    .catch((err) => {
                    });
                }
                else{
                    this.form.productItems.push(this.setItemModel({}));
                    //this.form.serviceItems.push(this.setServiceModel({}));
                }  
            },

            onAddNewProduct(){
                this.form.productItems.push(this.setItemModel({}));  
            },

            onAddNewService(){
                this.form.serviceItems.push(this.setServiceModel({}));  
            },


            onRemoveProduct(item_no)
            {
                this.form.productItems.splice(item_no,1);
                this.getTotal();
            },

            onRemoveService(item_no)
            {
                this.form.serviceItems.splice(item_no,1);
                this.getTotal();
            },

            updateText(item){
                this.fil.store = this.user_store;
                this.fil.inventory = item.product_id;
                console.log(this.fil)
                axios.get("/api/store/getnumber", { params: this.fil })
                .then(({ data }) => {
                    if(Number(item.qty) > Number(data)){
                        Swal.fire(
                            'Failed!',
                            'You do not have upto that product!',
                            'error'
                        ) 
                        let item_no = this.form.productItems.indexOf(item);
                        this.form.productItems.splice(item_no,1);
                    }
                    this.getTotal();
                });
            },

            updateDiscount(item){
                var disc = (Number(item.price))-(Number(item.discount)/100) * Number(item.price)

                if(disc <= Number(item.cost_price)){
                    var message = 'You cannot sell above the cost price';
                    Swal.fire(
                        'Failed!',
                        message,
                        'error'
                    )                 
                }

                else if(Number(item.discount) > Number(this.discount)){
                    var message = 'You cannot give more than this discount, your maximum discount rate is '+ this.discount + '%';
                    Swal.fire(
                        'Failed!',
                        message,
                        'error'
                    )                 
                }

                else{
                    this.getTotal();
                }

                
            },

            getTotal(){
                axios.post('/api/store/gettotal', this.form)
                .then(({ data }) => {
                    this.form.amount = data;
                })
                .catch();
            },

            setProduct(product, index){      
                if((product.number <= 0) || (product.number == null)){
                    Swal.fire(
                        'Failed!',
                        'You do not have enough product!',
                        'error'
                    )                 
                }
                else{
                    this.setItemModel(index, product);
                    this.getTotal();
                }
            },

            setItemModel(model, newModel){
                
                model.id = newModel !== undefined ? newModel.id: 0;
                model.price = newModel !== undefined ? newModel.price: 0;
                model.cost_price = newModel !== undefined ? newModel.cost_price: 0;
                model.product_name = newModel !== undefined ? newModel.product_name: '';
                model.product_id = newModel !== undefined ? newModel.product_id: '';
                model.number = newModel !== undefined ? newModel.number: 0;
                model.discount = model.discount !== undefined ? model.discount: 0;
                model.qty = model.qty !== undefined ? model.qty : 1;
                return model;
            },

            setServiceModel(model, newModel){
                model.id = newModel !== undefined ? newModel.id: 0;
                model.price = newModel !== undefined ? newModel.price: 0;
                model.title = newModel !== undefined ? newModel.title: '';
                return model;
            },

            getUserID(data){
                this.form.user_id = data.id;
            },


            CreateCustomer()
            {
                this.$refs.formCustomer.show();
            },

            onCustomerSubmit($event)
            {
                this.formCustomer.post("/api/customer")

                .then((data) => {
                    this.$refs.formCustomer.hide();
                    Swal.fire(
                        "Created!",
                        "Customer Created Successfully.",
                        "success"
                    );
                })

                .catch((err) => {
                    this.show_error(err.response.data.errors);
                });
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
        },
     }  
</script>



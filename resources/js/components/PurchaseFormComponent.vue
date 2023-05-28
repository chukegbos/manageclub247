<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Item Purchase</strong></h2>
                </div>
               
            </div>

            <b-form @submit.stop.prevent="is_edit ? updateForm() : submitRequest()">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type of Purchase</label>
                                    <select v-model="form.mop" class="form-control">
                                        <option v-for="option in options" v-bind:value="option.value" :key="option.id">
                                            {{ option.text }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Supplier</label>
                                    
                                    <v-select label="supplier_name" :options="suppliers" @input="setSelected" ></v-select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date of Purchase</label>
                                    <b-form-input v-model="form.purchase_date" type="date" class="form-control"></b-form-input>
                                </div>
                            </div>
                        </div>

                        <table class="table table-responsive-md ">
                            <thead>
                                <tr>
                                    <th width="150px">Product</th>
                                    <th>Pack</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost Price (<span v-html="nairaSign"></span>/Crate)</th>
                                    <th>Total Cost Price (<span v-html="nairaSign"></span>/Crate)</th>
                                    <th width="150px">Unit Cost Price (<span v-html="nairaSign"></span>/Bottle)</th>
                                    <th>Unit Selling Price (<span v-html="nairaSign"></span>/Bottle)</th>
                                    <th width="20px"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item) in form.productItems"  :key="item.id">
                                    <td>
                                        <span v-if="item.purchaseId">{{ item.product_name }}</span>
                                      
                                        <v-select label="product_name" :options="products" @input="setProduct($event,item)" v-else></v-select>
                                    </td>

                                    <td>
                                        <b-form-input v-model="item.quantity" type="number" class="form-control" step=".01"></b-form-input></td>

                                    <td>
                                        <span class="btn btn-secondary">{{ item.quantity * item.number_per_crate }}</span>
                                    </td>


                                    <td>
                                        <b-form-input v-model="item.unit_amount" type="number" class="form-control" step=".01"></b-form-input>
                                    </td>

                                    <td>
                                        <span class="btn btn-secondary">{{ item.unit_amount * item.quantity }}</span>
                                    </td>


                                    <td>
                                        <span class="btn btn-secondary">{{ formatPrice(item.unit_amount/item.number_per_crate) }}</span>
                                    </td>

                                    

                                   

                                    <td>
                                        <b-form-input v-model="item.price" type="number" class="form-control" step=".01"></b-form-input>
                                    </td>


                                    <td>
                                        <button type="button" class="btn btn-tool" @click="onRemoveProduct(form.productItems.indexOf(item))">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-8">
                        <b-button variant="outline-primary" size="sm" @click="onAddNewProduct">
                            <i class="fa fa-plus"></i> New Field
                        </b-button>

                        <b-button variant="outline-primary" size="sm" @click="newModal">
                            Create Product
                        </b-button>

                        <b-button variant="outline-primary" size="sm" @click="newSupModal">
                            Create Supplier
                        </b-button>
                    </div>

                    <div class="col-md-4">
                        <b-button type="submit" variant="primary" class="pull-right" size="sm">
                            <i class="fa fa-save"></i> Save All
                        </b-button>
                    </div>
                </div>
            </b-form> 

            <div class="modal fade" id="addNewInventory" tabindex="-1" role="dialog" aria-labelledby="addNewInventoryLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewInventoryLabel"
                            >
                                New Product
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="createInventory()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input v-model="formItem.product_name" type="text" name="product_name" class="form-control" :class="{'is-invalid': formItem.errors.has('product_name')}"/>
                                    <has-error :form="formItem" field="product_name"></has-error>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Unit of Measurement</label>
                                    <input v-model="formItem.unit" type="text" name="unit" class="form-control" :class="{'is-invalid': form.errors.has('unit')}"/>
                                    <has-error :form="formItem" field="unit"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Number Per Pack</label>
                                    <input v-model="formItem.number_per_crate" type="number" name="number_per_crate" class="form-control" :class="{'is-invalid': form.errors.has('number_per_crate')}" required/>

                                    <has-error :form="formItem" field="number_per_crate"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Cost Price</label>
                                    <input v-model="formItem.cost_price" type="number" name="cost_price" class="form-control" :class="{'is-invalid': form.errors.has('cost_price')}" required/>

                                    <has-error :form="formItem" field="cost_price"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Selling Price</label>
                                    <input v-model="formItem.price" type="number" name="price" class="form-control" :class="{'is-invalid': formItem.errors.has('price')}"/>
                                    <has-error :form="formItem" field="price"></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Select Category</label>
                                    <b-form-select
                                        v-model="formItem.category"
                                        :options="categories"
                                        value-field="id"
                                        text-field="name"
                                    >
                                    <template v-slot:first>
                                        <b-form-select-option :value="null" disabled>
                                            -- Please select category--
                                        </b-form-select-option>
                                    </template>
                                    </b-form-select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Create
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div
                class="modal fade"
                id="addNewSup"
                tabindex="-1"
                role="dialog"
                aria-labelledby="addNewUserLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewUserLabel"
                            >
                                New Supplier
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form
                            @submit.prevent="createUser()
                            "
                        >
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name of Supplier</label>
                                    <input
                                        v-model="formSup.supplier_name"
                                        type="text"
                                        name="supplier_name"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': formSup.errors.has(
                                                'supplier_name'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="formSup"
                                        field="supplier_name"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Contact Person</label>
                                    <input
                                        v-model="formSup.contact_person"
                                        type="text"
                                        name="contact_person"
                                        required
                                        class="form-control"
                                        :class="{
                                            'is-invalid': formSup.errors.has(
                                                'contact_person'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="formSup"
                                        field="contact_person"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input
                                        v-model="formSup.email"
                                        type="email"
                                        name="email"
                                        required
                                        class="form-control"
                                        placeholder="Email Address"
                                        :class="{
                                            'is-invalid': formSup.errors.has(
                                                'email'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="formSup"
                                        field="email"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input
                                        v-model="formSup.phone"
                                        type="tel"
                                        required
                                        class="form-control"
                                        placeholder="Phone Number"
                                        :class="{
                                            'is-invalid': formSup.errors.has(
                                                'phone'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="formSup"
                                        field="phone"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <input
                                        v-model="formSup.address"
                                        type="text"
                                        required
                                        class="form-control"
                                        placeholder="Address"
                                        :class="{
                                            'is-invalid': formSup.errors.has(
                                                'address'
                                            )
                                        }"
                                    />
                                    <has-error
                                        :form="formSup"
                                        field="address"
                                    ></has-error>
                                </div>

                                <div class="form-group">
                                    <label>Account Number</label>
                                    <input
                                        v-model="formSup.bank_account"
                                        class="form-control"
                                        @change="onType()"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Select Bank</label>
                                    <b-form-select
                                        v-model="formSup.bank_name"
                                        :options="banks"
                                        value-field="code"
                                        text-field="name"
                                        label="Select Bank"
                                        v-on:change="onSet($event)"
                                    >
                                        <template v-slot:first>
                                            <b-form-select-option
                                                :value="null"
                                                disabled
                                                >-- Please select your
                                                bank--</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>

                                <div class="form-group">
                                    <label>Account Name</label>
                                    <input
                                        v-model="formSup.account_name"
                                        readonly="true"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    data-dismiss="modal"
                                >
                                    Close
                                </button>
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Create
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
            this.loadInventory();
            this.getBank();
            this.loadSite();
        },

        data(){
            return{
                products: [],
                stores: [],
                suppliers: [],
                purchase_id: '',
                form: new Form({
                    productItems: [],
                    mop: 1,
                    purchase_date: '',
                    supplier: '',
                }),
                nairaSign: "&#x20A6;",
                bank_detail: {
                    bank_id: "",
                    bank_account: ""
                },

                banks: {},

                formItem: new Form({
                    id: "",
                    product_name: "",
                    price: "0",
                    cost_price: "",
                    category: 1,
                    unit: '',
                    number_per_crate: '',
                }),
                formSup: new Form({
                    id: "",
                    supplier_name: "",
                    contact_person: "",
                    email: "",
                    phone: "",
                    address: "",
                    bank_name: null,
                    account_name: "",
                    bank_account: ""
                }),
                site: '',
                is_edit: false,
                edit_model: null,
                is_busy: false,
                typeQuery: '',
                accounts: [],
                options: [
                    { text: 'Cash Payment', value: '1' },
                    { text: 'Credit Payment', value: '0' },
                ],
                categories: [],
                purchase: '',
            }
        },

        methods: {
            setSelected(value) {
                this.form.supplier = value.id;
            },

            getBank() {
                axios.get("/api/user/bank")
                .then(({ data }) => {
                    this.banks = data;
                });
            },

            onType() {
                this.bank_detail.bank_account = this.formSup.bank_account;
            },

            onSet(event) {
                this.bank_detail.bank_id = event;
                if (this.is_busy) return;
                this.is_busy = true;
                axios
                    .post("/api/user/fetchbank", this.bank_detail)
                    .then(({ data }) => {
                        if (data.data.account_name == "error") {
                            Swal.fire(
                                "Failed!",
                                "Such account do not exist, check to see if you are correct",
                                "error"
                            );
                        } else {
                            this.formSup.account_name = data.data.account_name;
                        }
                    })
                    .catch(err => {
                        Swal.fire(
                            "Failed!",
                            "Such account do not exist, check to see if you are correct",
                            "error"
                        );
                    })

                    .finally(() => {
                        this.is_busy = false;
                    });
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadUsers();
            },

            createUser() {
                this.$Progress.start();

                axios.post("/api/suppliers", this.formSup)
                .then(() => {
                    

                    Swal.fire(
                        "Created!",
                        "Supplier Created Successfully.",
                        "success"
                    );
                    $("#addNewSup").modal("hide");
                    this.formSup = [];
                    this.loadInventory();
                    this.getUser();
                    
                    this.loadUsers();
                    this.getBank();
                    this.loadSite();
                })

                .catch(() => {
                    this.$Progress.fail();
                });
            },

            newModal() {
                this.formItem.reset();
                $("#addNewInventory").modal("show");
            },

            newSupModal() {
                this.formItem.reset();
                $("#addNewSup").modal("show");
            },

            loadInventory() {
                axios.get("/api/inventory", { params: this.filterForm })

                .then(({ data }) => {
                    this.categories = data.categories;
                });
            },

            createInventory() {
                if (this.is_busy) return;
                this.is_busy = true;

                this.formItem.post("/api/inventory")
                .then(() => {
                    this.is_busy = false;
                    Swal.fire(
                        "Created!",
                        "Item Created Successfully.",
                        "success"
                    );
                    $("#addNewInventory").modal("hide");
                    this.loadInventory();
                    this.getUser();
                    this.loadUsers();
                    this.getBank();
                    this.loadSite();

                })

                .catch(error => {
                    this.is_busy = false;
                    if (error.response.data.error == "Item Already Exist") {
                        Swal.fire(
                            "Failed!",
                            "Inventory Already Exist",
                            "error"
                        );
                    }
                });
            },

            loadSite() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                })
                .catch()
                .finally(() => {
                    this.is_busy = false;
                });
            },

            submitRequest(event) {
                this.purchase_id = this.$route.params.purchase_id;

                var error = [];
                if(this.form.purchase_date == '')
                {
                    Swal.fire(
                        "Failed!",
                        'Fill the date',
                        "warning"
                    );
                }
                else
                {
                    this.form.productItems.forEach(function (product) {
                        if((product.product_name == '') || (product.quantity == ''))
                        {
                            error.push(product);
                            Swal.fire(
                                "Failed!",
                                'Fill all the fields',
                                "warning"
                            );
                        }             
                    });

                    if(error.length==0){

                        if(this.is_busy) return;
                        this.is_busy = true;

                        this.form.post("/api/purchases")
                        .then((data) => {

                            if(data.data.error){
                                Swal.fire(
                                    "Failed!",
                                    data.data.error,
                                    "warning"
                                );
                            }
                            
                            else
                            {
                                Swal.fire(
                                    "Created!",
                                    "Successfully Done.",
                                    "success"
                                );
                                //this.$router.push({ path: '/admin/purchases/' + data});
                                this.$router.push({ path: '/admin/purchases'});
                            }
                        })

                        .catch(error => {
                            if(error.message && (error.message=='Trying to get property balancing_account of non-object')){
                                Swal.fire(
                                    "Failed!",
                                    'Error! Please go to settings and set the balancing account systems.',
                                    "warning"
                                );
                            }
                            else {
                                Swal.fire(
                                    "Failed!",
                                    "There is error somewhere",
                                    "error"
                                );
                            }
                        })
                        .finally(() => {
                            this.is_busy = false;
                        });
                    }
                }
            },

            updateForm(event) {        
                var error = [];
                if(this.form.purchase_date == '')
                {
                    Swal.fire(
                        "Failed!",
                        'Fill the date',
                        "warning"
                    );
                }
                else
                {
                    this.form.productItems.forEach(function (product) {
                        if(product.quantity == '')
                        {
                            error.push(product);
                            Swal.fire(
                                "Failed!",
                                'Fill all the quantities',
                                "warning"
                            );
                        }             
                    });

                    if(error.length==0){
                        if(this.is_busy) return;
                        this.is_busy = true;

                        axios.put('/api/purchases/' + this.purchase.id, this.form)
                        .then((data) => {

                            if(data.data.error){
                                Swal.fire(
                                    "Failed!",
                                    data.data.error,
                                    "warning"
                                );
                            }
                            else
                            {
                                Swal.fire(
                                    "Created!",
                                    "Successfully Done.",
                                    "success"
                                );
                          
                                this.$router.push({ path: '/admin/purchases/' + this.purchase.purchase_id});
                            }
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
                    }
                }
            },

            onAddNewProduct(){
                this.form.productItems.push(this.setItemModel({}));  
            },

            onAddProduct(){

                this.purchase_id = this.$route.params.purchase_id;
                
                if (this.is_busy) return;
                this.is_busy = true;
                if(this.purchase_id){
                    axios.get("/api/purchases/" + this.purchase_id)

                    .then((response) => {
                        if(response.data.error){
                            Swal.fire(
                                "Failed!",
                                'Purchase Order does not exist, create new order.',
                                "warning"
                            );
                            this.form.productItems.push(this.setItemModel({}));
                        }
                        else
                        {
                            this.is_edit = true;
                            this.form.productItems = response.data.set_purchases;
                            this.form.purchase_date = response.data.purchase.purchase_date;
                            this.form.supplier = response.data.supplier;
                            this.purchase = response.data.purchase;
                            if(response.data.purchase.mop=='Cash Payment'){
                                this.form.mop = 1;
                            }
                            else {
                                this.form.mop = 0;
                            }
                        }
                    })

                    .catch((err) => {
                    });
                }

                else {
                    this.form.productItems.push(this.setItemModel({}));
                }
                
                this.is_busy = false; 
            },

            onRemoveProduct(item_no)
            {
                this.form.productItems.splice(item_no,1);
            },

            addProductItem(product, index){
                let selectedItem = this.form.productItems[index];
                this.setItemModel(selectedItem, product);
            },

            setItemModel(model, newModel){

                console.log(model)
                console.log(newModel)
                model.id = newModel !== undefined ? newModel.id: 0;
                model.product_name = newModel !== undefined ? newModel.product_name: '';
                model.quantity = model.quantity !== undefined ? model.quantity : 1;
                model.number_per_crate = model.number_per_crate !== undefined ? newModel.number_per_crate : 12;
                model.amount = model.amount !== undefined ? (model.unit_amount * model.quantity) : 1;

                model.unit_amount = model.unit_amount !== undefined ? (model.number_per_crate * model.quantity * newModel.cost_price) : 1;

                model.price = newModel !== undefined ? newModel.price: 0;
                return model;
            },
               


            setProduct(product, value) {
                this.setItemModel(value, product);
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                console.log(data.products)
                    this.products = data.products;
                    this.stores = data.stores;
                    this.suppliers = data.suppliers;
                    this.accounts = data.accounts;
                });
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
        },
     }  
</script>



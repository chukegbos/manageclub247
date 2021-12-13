<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Non Item Purchase</strong></h2>
                </div>
               
            </div>

            <b-form @submit.stop.prevent="submitRequest()">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Type of Purchase</label>
                                    <select v-model="form.mop" class="form-control">
                                        <option v-for="option in options" v-bind:value="option.value">
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
                                    <th width="600px">Product</th>
                                    <th width="150px">Quantity</th>
                                    <th width="200px">Amount</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in form.productItems">
                                    <td><b-form-input v-model="item.product_name" type="text" class="form-control" placeholder="Purchase of fuel"></b-form-input></td>

                                    <td><b-form-input v-model="item.quantity" type="number" class="form-control" step=".01"></b-form-input></td>

                                    <td><b-form-input v-model="item.amount" type="number" class="form-control" step=".01"></b-form-input></td>

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
                        <b-button variant="outline-primary" size="sm" @click="onAddProduct">
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
                                
                                <div class="form-group">
                                    <label>Selling Price</label>
                                    <input v-model="formItem.price" type="number" name="price" class="form-control":class="{'is-invalid': formItem.errors.has('price')}"/>
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
                                                bank--</b-form-select-option
                                            >
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
            this.loadSite();
            this.getUser();
            this.loadInventory();
            this.getBank();
            this.getBank();
        },

        data(){
            return{
                products: [],
                stores: [],
                suppliers: [],
                form: new Form({
                    productItems: [],
                    mop: 1,
                    purchase_date: '',
                    supplier: '',
                }),

                bank_detail: {
                    bank_id: "",
                    bank_account: ""
                },

                banks: {},

                formItem: new Form({
                    id: "",
                    product_name: "",
                    price: "0",
                    category: 1,
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
                is_busy: false,
                typeQuery: '',
                accounts: [],
                options: [
                    { text: 'Cash Purchase', value: '1' },
                    { text: 'Credit Purchase', value: 'o' },
                ],
                categories: [],
            }
        },

        methods: {
            setSelected(value) {
                this.form.supplier = value.id;
            },

            getBank() {
                axios.get("/api/user/bank")
                .then(({ data }) => {
                    console.log(this.banks);
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
                        console.log(data);

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
                    this.loadSite();
                    this.loadUsers();
                    this.getBank();
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
                    this.loadSite();
                    this.loadUsers();
                    this.getBank();
                })

                .catch(error => {
                    this.is_busy = false;
                    if (
                        error.response.data.error == "Item Already Exist"
                    ) {
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
                console.log(this.form);
                var error = [];
                if((this.form.supplier == '') || (this.form.purchase_date == ''))
                {
                    Swal.fire(
                        "Failed!",
                        'Fill all the inputs',
                        "warning"
                    );
                }
                else
                {
                    console.log(this.form.productItems);
                    this.form.productItems.forEach(function (product) {
                        if((product.product_name == '') || (product.amount == ''))
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

                        this.form.post("/api/purchases/non")
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
                                this.$router.push({ path: '/admin/purchases'});
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

            onAddProduct(){
                this.form.productItems.push(this.setItemModel({}));
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
                model.id = newModel !== undefined ? newModel.id: '';
                model.product_name = newModel !== undefined ? newModel.product_name: '';
                model.quantity = model.quantity !== undefined ? model.quantity : 1;
                model.amount = model.amount !== undefined ? model.amount : 0;
                return model;
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.products = data.products;
                    this.stores = data.stores;
                    this.suppliers = data.suppliers;
                    this.accounts = data.accounts;
                });
            },
        },
     }  
</script>



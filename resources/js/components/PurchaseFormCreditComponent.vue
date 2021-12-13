<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Create Purchase</strong></h2>
                </div>
            </div>

            <b-form @submit.stop.prevent="submitRequest()">
                <div class="card" v-for="(item, index) in form.productItems">
                    <div class="card-header">
                        <h3 class="card-title">Product {{ index + 1 }}</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>

                            <button type="button" class="btn btn-tool" @click="onRemoveProduct(form.productItems.indexOf(item))">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                  
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Product</label>
                                    <v-select label="product_name" :options="products" @input="setProduct($event,item)" ></v-select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <b-form-input v-model="item.quantity" type="number" class="form-control" step=".01"></b-form-input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <b-form-input v-model="item.amount" type="number" class="form-control" step=".01"></b-form-input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Supplier</label>
                                    <b-form-select
                                        v-model="item.supplier"
                                        :options="suppliers"
                                        class="mb-3"
                                        value-field="id"
                                        text-field="supplier_name"
                                        disabled-field="notEnabled"
                                    ></b-form-select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date of Purchase</label>
                                    <b-form-input v-model="item.purchase_date" type="date" class="form-control"></b-form-input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Outlet to Move</label>
                                    <b-form-select
                                        v-model="item.store"
                                        :options="stores"
                                        class="mb-3"
                                        value-field="id"
                                        text-field="name"
                                        disabled-field="notEnabled"
                                    ></b-form-select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-1">
                    <b-button @click="onAddProduct" class="pull-right"><i class="fa fa-plus"></i> New Field
                    </b-button>
                    <b-button type="submit" variant="primary"><i class="fa fa-save"></i> Save All</b-button>
                </div>
            </b-form> 
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
        },

        data(){
            return{
                products: [],
                stores: [],
                suppliers: [],
                form: new Form({
                    productItems: [],
                    account: '3',
                }),
                site: '',
                is_busy: false,
                typeQuery: '',
                accounts: [],
            }
        },

        methods: {
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
                console.log(this.form)
                var error = [];
                this.form.productItems.forEach(function (product) {
                    if((product.product_name == '') || (product.quantity == '') || (product.amount == '') ||(product.store == '') ||(product.supplier == ''))
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
                model.store = model.store !== undefined ? model.store : '';
                return model;
            },

            setProduct(product, value) {
                this.setItemModel(value, product);
            },



            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.products = data.products;
                    this.stores = data.stores;
                    this.suppliers = data.suppliers;
                });
            },
        },
     }  
</script>



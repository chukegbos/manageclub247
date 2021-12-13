<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Create Purchase</strong></h2>
                </div>
            </div>
                
            <div> 
                <b-form @submit.stop.prevent="submitRequest()">
                    <div v-for="(item, index) in form.productItems">
                        <div class="box box-danger">
                            <div class="box-header with-border">
                                <h3 class="box-title">Product {{ index + 1 }}</h3>

                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><iclass="fa fa-minus"></i></button>
                                </div>
                            </div>

                            <div class="box-body">
                                <div class="d-md-flex">
                                    <div class="flex-grow-1 mr-4">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <b-form-group label-for="title">
                                                    Select Product: <span class="text-danger danger">*</span>

                                                  
                                                  

                                                    <vue-typeahead-bootstrap
                                                      v-model="item.product_name"
                                                      :ieCloseFix="false"
                                                      :data="products"
                                                      :serializer="data => data.product_name"
                                                      @hit="addProductItem($event, index)"
                                                      placeholder="Search for product"
                                                      @input="lookup(item)"
                                                    />
                                                </b-form-group>

                                                <b-form-group label-for="Quantity">
                                                    Quantity:

                                                    <b-form-input v-model="item.quantity" type="number" class="form-control" step=".01"></b-form-input>
                                                </b-form-group>

                                                <b-form-group label-for="Amounts">
                                                    Amount:

                                                    <b-form-input v-model="item.amount" type="number" class="form-control" step=".01"></b-form-input>
                                                </b-form-group>
                                            </div>

                                            <div class="col-lg-4">
                                                <b-form-group label-for="description">
                                                    Select Supplier:

                                                    <vue-typeahead-bootstrap
                                                      v-model="item.supplier_name"
                                                      :ieCloseFix="false"
                                                      :data="suppliers"
                                                      :serializer="data => data.supplier_name"
                                                      @hit="addSupplierItem($event, index)"
                                                      placeholder="Search for Supplier"
                                                      @input="lookSupplier(item)"
                                                    />
                                                </b-form-group>
                                            </div>

                                          

                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span @click="onRemoveProduct(form.productItems.indexOf(item))"><i class="fa fa-times text-red 2x"></i></span>
                                        
                                        <button type="button" @click="onRemoveProduct(index)"
                                                class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <b-button @click="onAddNewProduct" v-if="set_field"><i class="fa fa-plus"></i> Add New Product
                        </b-button>

                        <b-button type="submit" variant="primary"><i class="fa fa-save"></i> Save All</b-button>
                    </div>
                </b-form>            
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
        },

        data(){
            return{
                products: [],
                form: new Form({
                    productItems: [],
                    production_date: '',
                }),
                site: '',
                is_busy: false,
            }
        },

        methods: {
            lookup(item){
                debounce(() => {
                    fetch('/api/inventory', {params: item.product_name})
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        this.products = data.inventories;
                    })
                }, 500)();
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
                if(this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/production", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Successfully Done.",
                        "success"
                    );
                    this.$router.push({ path: '/production'});
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
                model.id = newModel !== undefined ? newModel.id: 0;
                model.product_name = newModel !== undefined ? newModel.product_name: '';
                model.quantity = model.quantity !== undefined ? model.quantity : 1;
                return model;
            },
        },
     }  
</script>



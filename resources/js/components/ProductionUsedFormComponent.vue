<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Process List</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" @click="onAddProduct" class="pull-right m-2">
                        Add Process
                    </b-button>
                </div>
            </div>

            <div class="card">
                <b-form @submit.stop.prevent="submitRequest()">
                    <div class="card-body">
                        <table class="table table-striped table-responsive-md text-center">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity Used</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr v-for="(item, index) in form.productItems">
                                    <td>
                                        <vue-typeahead-bootstrap
                                          v-model="item.product_name"
                                          :ieCloseFix="false"
                                          :data="products"
                                          :serializer="data => data.product_name"
                                          @hit="addProductItem($event, index)"
                                          placeholder="Search for product"
                                          @input="lookup(item)"
                                        />
                                    </td>
                                    <td>
                                        <b-form-input v-model="item.qty" max="item.quantity" type="number" class="form-control qty-input" @input="updateText(item)"></b-form-input>

                                    </td>
                                    <td>
                                        <span @click="onRemoveProduct(form.productItems.indexOf(item))"><i class="fa fa-times text-red 2x"></i></span>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-group">
                            <label>Date of Process</label>
                            <input v-model="form.process_date" type="date" name="process_date" class="form-control":class="{'is-invalid': form.errors.has('process_date')}"/>
                            <has-error :form="form" field="process_date"></has-error>
                        </div>
                    </div>
                    <div class="card-footer">
                        <b-button type="submit" variant="primary">Submit</b-button>
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
                    process_date: '',
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

                axios.post("/api/production/used", this.form)
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Successfully Done.",
                        "success"
                    );
                    this.$router.push({ path: '/production/used'});
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

            updateText(item){
                console.log(item.quantity)

                console.log(item.qty)
                if(item.qty > item.quantity){
                    Swal.fire(
                        'Failed!',
                        'You do not have upto that quantity!',
                        'error'
                    ) 
                    let item_no = this.form.productItems.indexOf(item);
                    this.form.productItems.splice(item_no,1);
                }
            },


            addProductItem(product, index){
                let selectedItem = this.form.productItems[index];
                
                if((product.quantity <= 0) || (product.quantity == null)){
                    Swal.fire(
                        'Failed!',
                        'You do not have enough product!',
                        'error'
                    )
                    
                }
                else
                {
                    this.setItemModel(selectedItem, product);
                }
            },

            setItemModel(model, newModel){
                console.log(newModel)
                model.id = newModel !== undefined ? newModel.id: 0;
                model.product_name = newModel !== undefined ? newModel.product_name: '';
                model.quantity = newModel !== undefined ? newModel.quantity: '';
                return model;
            },
        },
     }  
</script>



<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Item market</strong></h2>
                </div>
               
            </div>

            <b-form @submit.stop.prevent="is_edit ? updateForm() : submitRequest()">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-responsive-md ">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th width="200px">Quantity</th>
                                    <th width="200px">Amount (<span v-html="nairaSign"></span>)</th>
                                    <th width="20px"></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in form.productItems">
                                    <td>
                                        <v-select label="name" :options="foods" @input="setProduct($event,item)"></v-select>
                                    </td>

                                    <td>
                                        <b-form-input v-model="item.quantity" type="number" class="form-control"></b-form-input>
                                    </td>

                                    <td>
                                        <b-form-input v-model="item.amount" type="number" class="form-control" step=".01"></b-form-input>
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
                    <div class="col-md-4">
                        <b-button variant="outline-primary" size="sm" @click="onAddNewProduct">
                            <i class="fa fa-plus"></i> New Field
                        </b-button>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <b-form-input v-model="form.purchase_date" type="date" class="form-control" placeholder="Select Date"></b-form-input>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <b-button type="submit" variant="primary" class="pull-right" size="sm">
                            <i class="fa fa-save"></i> Save All
                        </b-button>
                    </div>
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
            this.getUser();
            this.loadSite();
        },

        data(){
            return{
                foods: [],
                stores: [],
                suppliers: [],
                market_id: '',
                form: new Form({
                    productItems: [],
                    purchase_date: '',
                }),
                nairaSign: "&#x20A6;",
           
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
                market: '',
            }
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadUsers();
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
                this.market_id = this.$route.params.market_id;

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
                        if((product.item == '') || (product.quantity == '') || (product.amount == ''))
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

                        this.form.post("/api/markets")
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
                                this.$router.push({ path: '/kitchen/market'});
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

            updateForm(event) {
                var error = [];
                if(this.form.market_date == '')
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

                        axios.put('/api/markets/' + this.market.id, this.form)
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
                          
                                this.$router.push({ path: '/admin/markets/' + this.market.market_id});
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

                this.market_id = this.$route.params.market_id;
                
                if (this.is_busy) return;
                this.is_busy = true;
                if(this.market_id){
                    axios.get("/api/markets/" + this.market_id)
                    .then((response) => {
                        if(response.data.error){
                            Swal.fire(
                                "Failed!",
                                'Market Order does not exist, create new order.',
                                "warning"
                            );
                            this.form.productItems.push(this.setItemModel({}));
                        }
                        else
                        {
                            this.is_edit = true;
                            this.form.productItems = response.data.set_markets;
                            this.form.market_date = response.data.market.market_date;
                            this.market = response.data.market;
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
                model.item = newModel !== undefined ? newModel.item: '';
                model.quantity = newModel !== undefined ? newModel.quantity: '';
                model.amount = 0;
                return model;
            },
               


            setProduct(product, value) {
                this.setItemModel(value, product);
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.foods = data.items;
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



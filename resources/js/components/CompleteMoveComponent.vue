<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
     
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Fill Quantity</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <b-form @submit.stop.prevent="onFormSubmit()">
                        <table class="table table-striped table-responsive-md">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th width="100px">Quantity</th>
                                    <th>Available</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in form.productItems">
                                    <td>{{ item.title }}</td>
                                    <td>
                                        
                                        <b-form-input v-model="item.qty" value="1" max="item.number" type="number" class="form-control qty-input"  @input="updateText(item)"></b-form-input>
                                      
                                    </td>
                                    <td>{{ item.number }}</td>
                                    <!--<td>
                                        <a href="javascript:void(0)" @click="onRemoveProduct(form.productItems.indexOf(item))"><i class="fa fa-times text-red 2x"></i></a>
                                    </td>-->
                                </tr>
                            </tbody>
                        </table>
                        <b-form-group label="Receiving Outlet:">
                            <v-select label="name" :options="stores" @input="setSelected" ></v-select>
                        </b-form-group>
                        <b-button type="submit" variant="primary">Submit</b-button>
                    </b-form>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import axios from 'axios';
    export default {
        created(){
            this.onAddProduct();
            this.getUser();
        },

        data(){
            return{
                form: new Form({
                    productItems: [],
                    store: '',
                }),
               
                is_busy: false,
                stores: [],
                errors: [],
            }
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.user = data.user;
                    this.stores = data.stores;
                });
            },

            setSelected(value) {
                this.form.store = value.id;
            },

            onFormSubmit($event){
            console.log(this.form)
                if(this.form.store=='')
                {
                    Swal.fire(
                        "Failed!",
                        'Please, select an outlet',
                        "warning"
                    );
                }

                else if (this.form.store==this.user.store)
                {
                    Swal.fire(
                        "Failed!",
                        'You cannot select your own outlet',
                        "warning"
                    );
                }
                else {
                    var error = [];
                    this.form.productItems.forEach(function (product) {
                        if(product.qty==null)
                        {
                            error.push(product);
                            Swal.fire(
                                "Failed!",
                                'Fill all the fields',
                                "warning"
                            );
                        } 

                        if(product.qty > product.number)
                        {
                            error.push(product);
                            Swal.fire(
                                "Failed!",
                                'One product quantity you selected is greater than what you have in your outlet!',
                                "warning"
                            );
                        }             
                    });

                    if(error.length==0){

                        if(this.is_busy) return;
                        this.is_busy = true;

                        axios.post('/api/movement', this.form)
                        .then((data)=>{
                            Swal.fire(
                                'Created!',
                                'Created Successfully.',
                                'success'
                            )
                            this.$router.push({ path: '/movements/myoutlet'});
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
                this.ref_id = this.$route.params.ref_id;
                if(this.ref_id){
                    axios.get('/api/movement/' + this.ref_id)
                    .then(({ data }) => {
                        this.form.productItems = data.products;
                        console.log(data)
                    });
                }
                else{
                    this.$router.push({ path: "/404"});
                } 
            },

            onRemoveProduct(item_no)
            {
                this.form.productItems.splice(item_no,1);
                axios.post('/api/store/gettotal', this.form)

                .then(({ data }) => {
                    this.form.amount = data;
                    this.form.totalPrice = this.form.amount - this.form.discount;
                })
                .catch();
            },

            updateText(item){
                if(item.qty > item.number){
                    Swal.fire(
                        "Oops",
                        "Select value less than the available quantity",
                        "error"
                    );
                }
            },

            setItemModel(model, newModel){
                model.id = newModel !== undefined ? newModel.id: 0;
                model.number = newModel !== undefined ? newModel.number: 0;
                model.product_name = newModel !== undefined ? newModel.product_name: 0;
                model.qty = newModel !== undefined ? newModel.number: 0;
                return model;
            },
        },
     }  
</script>



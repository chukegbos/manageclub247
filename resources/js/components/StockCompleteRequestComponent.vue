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
                                    <th width="400px">Product</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(item, index) in form.productItems">
                                    <td>{{ item.title }}</td>
                                    <td>
                                        <b-form-input v-model="item.quantity" type="number" class="form-control qty-input"></b-form-input>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                user: '',
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

            onFormSubmit($event){
                var error = [];
                this.form.productItems.forEach(function (product) {
                    if(product.quantity==null)
                    {
                        error.push(product);
                        Swal.fire(
                            "Failed!",
                            'Fill all the quantity fields',
                            "warning"
                        );
                    }            
                });

                if(error.length==0){
                    if(this.is_busy) return;
                    this.is_busy = true;

                    axios.post('/api/kitchen/movement/req', this.form)
                    .then((data)=>{
                        Swal.fire(
                            'Created!',
                            'Created Successfully.',
                            'success'
                        )
                        this.$router.push({ path: '/kitchen/movements/myrequest'});
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
                this.ref_id = this.$route.params.ref_id;
                if(this.ref_id){
                    axios.get('/api/kitchen/movement/' + this.ref_id)
                    .then(({ data }) => {
                        this.form.productItems = data.products;
                        console.log(this.form.productItems)
                    });
                }
                else{
                    this.$router.push({ path: "/404"});
                } 
            },
        },
     }  
</script>



<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">          
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Customer Sales</strong></h2>
                </div>
                <div class="col-md-6">
                    <vue-typeahead-bootstrap
                        v-model="userQuery"
                        :ieCloseFix="false"
                        :data="users"
                        :serializer="data => data.name"
                        @hit="getUserID($event)"
                        placeholder="Search for customer"
                        @input="lookUser"
                    />
                </div> 

                <div class="col-md-10">
                    <b-form @submit.stop.prevent="onFilterSubmit" size="sm">
                        <b-input-group>
                            <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                            </b-form-datepicker>

                            <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                            </b-form-datepicker>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>  
                </div>
                <div class="col-md-2">
                    <b-button size="sm" variant="outline-info" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>
                </div> 
            </div>

            <div class="card" id="printMe" v-if="unique_id ">
                <div class="card-header text-center">
                    <h6>{{ user.name }} Sales Report as at {{ filterForm.start_date | myDate }} to {{ filterForm.end_date | myDate }}</h6>
                    <p>
                        {{ user.address }} {{ user.city }} {{ user.state }}<br>
                        {{ user.email }}<br>
                        {{ user.phone }}
                    </p>
                </div>
               
                <div class="card-body">
                    <table class="table table-hover" style="height:10px">
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Name of product</th>
                            <th>Quantity</th>
                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                        </tr>

                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.date | myDate }}</td> 
                            <td>{{ item.ref_id }}</td> 
                            <td>{{ item.name  }}</td>
                            <td>{{ item.qty  }}</td>
                            <td>{{ formatPrice(item.amount)  }}</td>
                        </tr>
                    </table>
                </div>
            </div> 
        </div>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';
    import {debounce} from 'lodash';
    export default {
        created() {
            this.loadSite();
            this.loadPage();
        },

        data() {
            return {
                is_busy: false,
                filterForm: {
                    start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().add(1, 'days').format("YYYY-MM-DD"),
                },
                user: '',
                unique_id: '',
                item_count: '',
                items: [],
                nairaSign: "&#x20A6;",
                users: [],
                userQuery: '',
                time: 1,
            };
        },

        methods: {
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

            getUserID(data){
                if(this.is_busy) return;
                this.is_busy = true;

                this.unique_id = data.unique_id;
                this.userQuery = data.name;


                axios.get('/api/customer/' + this.unique_id, { params: this.filterForm })
                .then((response) => {

                    if(response.data.error)
                    {
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/customers"});
                    }
                    else
                    {
                        this.items = response.data.items;
                        this.item_count = response.data.item_count;
                        this.user = response.data.user;
                    }               
                });
                this.is_busy = false;
            },

            loadPage(){
                if(this.is_busy) return;
                this.is_busy = true;

                if(this.$route.params.unique_id){
                    this.unique_id = this.$route.params.unique_id;

                    axios.get('/api/customer/' + this.unique_id, { params: this.filterForm })
                    .then((response) => {
                        if(response.data.error){
                            Swal.fire(
                                "Failed!",
                                response.data.error,
                                "warning"
                            );
                            this.$router.push({ path: "/admin/customers"});
                        }
                        else{

                            this.items = response.data.items;
                            this.item_count = response.data.item_count;
                            this.user = response.data.user; 
                            console.log(this.items)
                        }    
                    })

                    .catch((err) => {
                        console.log(err);
                    });
                }

                this.is_busy = false;
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            onFilterSubmit()
            {
                this.loadSite();
                this.loadPage();
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.$htmlToPaper('printMe');
                this.is_busy = false;
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            newOrder(){
                this.filterForm.time= 1;
                this.loadPurchase();
                this.getUser();
            },

            oldOrder(){
                this.filterForm.time= 0;
                this.loadPurchase();
                this.getUser();
            },
        }
    };
</script>

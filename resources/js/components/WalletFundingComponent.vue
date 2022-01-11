<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">   
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Member Funding History</strong></h2>
                </div>

                <div class="col-md-6">
                    <router-link to="/admin/customers/fund" class="pull-right m-2 btn btn-primary btn-sm" v-if="admin.role==1 || admin.role==3 ||admin.role==4 || admin.role==1 || admin.role==4 || admin.role==5 || admin.role==11">
                        Fund Member
                    </router-link>

                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal  class="pull-right m-2">
                        <i class="fa fa-filter"></i> Filter
                    </b-button>
                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <div class="row">
                                <div class="col-lg-6">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>

                                <div class="col-lg-6">

                                    <b-form-group label="End Date:" label-for="End Date">
                                        <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>
                            </div>

                            <b-form-group label="Members:">
                                <!--<vue-typeahead-bootstrap
                                  v-model="userQuery"
                                  :ieCloseFix="false"
                                  :data="users"
                                  :serializer="data => data.name"
                                  @hit="getUserID($event)"
                                  placeholder="Search for customer"
                                  @input="lookUser"
                                />-->
                                <v-select label="get_member" :options="users" @input="setCustomer($event)"></v-select>
                            </b-form-group>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                      
                </div>
            </div>
        
            <div class="card">
                <div class="card-body table-responsive p-0" v-if="fundings.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>Member</th>
                            <th>Date</th>
                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                            <th>Wallet Funded</th>
                            <!--<th>Payment Type</th>-->
                            <th>Initiator</th>
                            <th>Approved By</th>
                            <th>Status</th>
                        </tr>

                        <tr v-for="fund in fundings.data" :key="fund.id">
                            <td>{{ fund.customer_id.last_name }} {{ fund.customer_id.first_name }}</td>
                            <td>{{ fund.created_at | myDate }}</td> 
                            <td>{{ formatPrice(fund.amount) }}</td>
                            <td>
                                <span v-if="fund.wallet==1">Membership Wallet</span>
                                <span v-else>Bar/Kitchen Wallet</span>
                            </td>  

                            <!--<td>{{ fund.mop }} <br><span v-if="fund.tran_type">({{ fund.tran_type }})</span></td> -->
                            <td>{{ fund.user_id }}</td> 
                            <td>{{ fund.approved_by }}</td>
                                  
                            <td>
                                {{ fund.status }} <a href="javascript:void(0)" @click="viewReceipt(fund)">Receipt</a>
                                
                                <span v-if="(admin.role==1 || admin.role==10 || admin.role==11) && fund.status=='Pending'"> <br>
                                    <a href="javascript:void(0)" @click="approve(fund)" class="text-green">Approve</a> | 
                                    <a href="javascript:void(0)" @click="reject(fund)" class="text-red">Reject</a>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No History Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Entries</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="fundings" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment';
    import axios from 'axios';
    import {debounce} from 'lodash';

    export default {
        created() {
            this.getUser();
            this.loadAccount();
            
        },

        data() {
            return {
                filterForm: {
                    end_date: '',
                    start_date: '',
                    customer_id: '',
                    selected: '10',
                    orderName: 1,
                    orderDate: 1,
                },
                nairaSign: "&#x20A6;",
                fundings: [],
                fundings: {
                    data: '',
                },
                admin: '',
                is_busy: false,
                userQuery: '',
                users: [],
                count_all: '',
            };
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.admin = data.user;
                    this.users = data.customers;
                    console.log(data)
                });
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadAccount();
                this.getUser();
            },

            getResults(page = 1) {
                axios.get("/api/funding?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.fundings = response.data.fundings;
                });
            },

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
                this.filterForm.customer_id = data.id;
            },

            setCustomer(user){
                this.filterForm.customer_id = user.id;
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/funding", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.fundings.data = data.fundings;
                    }
                    else{
                        this.fundings = data.fundings;
                    }
                    this.count_all = data.all;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            approve(fund) {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/funding/approve/'+fund.ref_id)
                .then(({ data }) => {
                   this.$router.push({ path: '/admin/funding/'+fund.ref_id})
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            reject(fund) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, reject it!"
                })
                .then(result => {
                    if (result.value) {

                        axios.get('/api/funding/reject/'+fund.ref_id)
                        .then(({ data }) => {
                            this.loadAccount();
                            this.getUser();
                            Swal.fire(
                                "Rejected!",
                                "The fund has been rejected.",
                                "success"
                            );
                           
                        });
                    }
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.getUser();
                this.$refs.filter.hide();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            viewReceipt(fund) {
                this.$router.push({ path: '/admin/funding/' + fund.ref_id})
            },

            orderByName() {
                if(this.filterForm.orderName==1) {
                   this.filterForm.orderName = 0; 
                }
                else {
                    this.filterForm.orderName = 1;
                }

                this.filterForm.orderDate = 2;
                this.loadAccount();
                this.getUser();
            },

            orderByDate() {
                if(this.filterForm.orderDate==1) {
                   this.filterForm.orderDate = 0; 
                }
                else {
                    this.filterForm.orderDate = 1;
                }

                this.filterForm.orderName = 2; 
                this.loadAccount();
                this.getUser();
            },
        }
    };
</script>

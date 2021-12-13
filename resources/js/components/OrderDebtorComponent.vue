<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-8">
                    <h3><strong>List of members' debt as at {{ filterForm.end_date | myDate}}</strong></h3>                   
                </div>

                <div class="col-md-4">

                    <b-button size="sm" variant="outline-info" class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>

                  
                    <download-csv
                        :data="all_data"
                        :name="dataFile"
                        :labels="labels"
                        :fields="fields"
                    >
                        <b-button size="sm" variant="outline-info" class="pull-right m-2"><i class="fa fa-file text-blue"></i> Download</b-button>
                    </download-csv>
            

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Filter" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <div class="row">
                                <!--<div class="col-md-12 form-group">
                                    <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search Member"></b-form-input>
                                </div>

                                <div class="col-lg-6">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>-->

                                <div class="col-lg-12">
                                    <b-form-group label="Select Date:">
                                        <b-form-datepicker v-model="filterForm.end_date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                </div>
                            </div>


                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                        
                </div>
            </div>

            <div class="card">
                <span id="printMe">
                    <div class="card-body table-responsive p-0"  v-if="unprintable==true">
                        <div class="text-center">
                            <h2>Enugu Sports Club - List of member debtors as at {{ filterForm.end_date }}</h2>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Member Name</th>
                                    <th>Member ID</th>
                                    <th>Phone</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in allusers" :key="user.id">
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.unique_id }}</td>
                                    <td>{{ user.phone }}</td>
                                    <td><span v-html="nairaSign"></span>{{ formatPrice(user.debt) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </span>

                <div class="card-body table-responsive p-0" v-if="users.data.length>0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Member ID</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Amount Owe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                
                                <td>{{ user.name }}</td>
                                <td>{{ user.unique_id }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{ user.phone }}</td>
                                
                                <td><span v-html="nairaSign"></span>{{ formatPrice(user.debt) }}</td>

                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Member Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Members</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="users" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>   
        </div>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';

    export default {
        created() {
            this.loadSales();
        },

        data() {
            return {
                filterForm: {
                    selected: '10',
                    start_date: '',
                    end_date: moment().format("YYYY-MM-DD"),
                },

                form: new Form({
                    id: "",
                    trans_id: "",
                    amount: "",
                    amount_paid: "",
                    purchase_date: "",
                }),

                options: [
                    { text: 'Paid', value: '1' },
                    { text: 'Not Paid', value: 'o' },
                ],

                payments: [
                  { value: 'Cash', text: 'Cash' },
                  { value: 'POS', text: 'POS' },
                  { value: 'Transfer', text: 'Transfer' },
                ],
                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                model: {},
                users: {},
                users: {
                    data: {},
                },
                allusers: {},
                action: {
                    selected: []
                },
                count_all: '',
                unprintable: false,

                all_data: [],
                dataFile: 'Debt Members.csv',
                labels: {
                    name: 'Name',
                    unique_id: 'Member ID',
                    phone: 'Phone',
                    debt: 'Amount',
                },
                fields : ['name', 'unique_id', 'phone', 'debt'],
                report_items: null,
            };
        },

        methods: {
            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
               
                this.unprintable = true;

                this.$htmlToPaper('printMe');
                //this.unprintable = false;
                this.is_busy = false;
               
                this.loadSales();
                this.getUser();            
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadSales();            
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            getResults(page = 1) {
                axios.get('/api/store/debtors?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.debtors = response.data.report_data;
                });
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/store/debtors', { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.users.data = data.customers;
                    }
                    else{
                        this.users = data.customers;
                    }
                    this.count_all = data.customers.total;
                    this.all_data = data.allusers;
                    this.allusers = data.allusers;
                    console.log(this.users)
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadSales();
                this.$refs.filter.hide();
            },
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }
</style>

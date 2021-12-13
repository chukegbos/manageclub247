<template>
    <b-overlay :show="is_busy" rounded="sm">

        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-5">
                    <h3>Book of Second Entry (Ledger)</h3>
                    <!--<h6 class="text-center"><strong>{{ site.sitename }} Ledger Balance <span v-if="account">({{ account.account }})</span> As At {{ filterForm.end_date |myDate }}</strong></h6>-->
                </div>

                <div class="col-md-7">
                    <!--<b-button size="sm" variant="outline-info" class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>-->

                    <b-button variant="outline-primary" size="sm" class="pull-right m-2" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
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
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                         
                </div>
            </div>

            <div class="card"  v-for="(ledgers, index) in all_accounts" v-if="all_accounts.length>0">
                <div class="text-center">
                    <span v-if="ledgers.data[0]"><h6><strong> {{ ledgers.data[0]['account'] }} Account</strong></h6></span>
                </div>
                <table class="table table-hover">
                    <tr>
                        
                        <th width="200px">Date</th>
                        <th width="300px">Particular</th>
                        
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>

                    <tr  v-for="(account, index) in ledgers.data" v-if="ledgers.data.length>0">
                        <td>{{ account.ledger_date | myDate }}</td>
                        <td>{{ account.description }}</td>
                        
                        <td>
                            <span v-if="account.position!=0">
                                <span v-if="account.debit>0">
                                    <span v-html="nairaSign"></span>{{ formatPrice(account.debit) }}
                                </span>
                            </span>
                        </td>

                        <td>
                            <span v-if="account.position!=0">
                                <span v-if="account.credit>0">
                                    <span v-html="nairaSign"></span>{{ formatPrice(account.credit) }}
                                </span>
                            </span>
                        </td> 
                              
                        <td>
                            <span v-if="account.position==0">
                                <span v-if="account.credit>0">
                                    <span v-html="nairaSign"></span>{{ formatPrice(account.credit) }}
                                </span>
                                <span v-if="account.debit>0">
                                    <span v-html="nairaSign"></span>{{ formatPrice(account.debit) }}
                                </span>
                            </span>
                        </td> 
                    </tr>
                </table>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-4">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                        </div>

                        <div class="col-md-8" v-if="filterForm.selected!=0">
                            <pagination :data="ledgers" @pagination-change-page="getResults"></pagination>
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
            this.loadAccount(); 
            this.getUser();
            this.loadSite();
        },

        data() {
            return {
                site: '',
                filterForm: {
                    end_date: moment().format("YYYY-MM-DD"),
                    start_date: '',
                    account: '',
                    selected: '10',
                },

              
                accounts: [],
                nairaSign: "&#x20A6;",
                ledgers: {},
                ledgers: {
                    data: '',
                },
                editMode: false,
                is_busy: false,
                account: '',
                form: new Form({
                    id: "",
                    ledger_date: '',
                    account: "",
                    amount: '',
                    description: '',
                }),

                action: {
                    selected: []
                },
                count_all: '',
                page: '',
                grouped_ledgers: [],
                all_accounts: [],
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.page = 0;
                this.loadAccount(); 
                this.getUser();

                this.loadSite();
            },

            setSelected(value) {
                this.form.account = value.id;
                console.log(value.id)
            },

            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
                this.unprintable = true;
                this.$htmlToPaper('printMe');
                this.unprintable = false;
                this.is_busy = false;
            },

            getResults(page = 1) {
                this.page = page
                axios.get("/api/ledger?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.ledgers = response.data.ledgers;
                    
                    this.all_accounts = response.data.all_accounts;
                });
            },

            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                
                if(this.$route.params.ref_id){
                    this.filterForm.account = this.$route.params.ref_id;
                }
                axios.get("/api/ledger", { params: this.filterForm })
                .then(({ data }) => {
                    console.log(data.all_accounts)
                    if(this.filterForm.selected==0)
                    {
                        this.ledgers.data = data.ledgers;
                    }
                    else{
                        this.ledgers = data.ledgers;
                    }
                    this.count_all = data.all;
                    this.account = data.account;
                    this.grouped_ledgers = data.grouped_ledgers;
                    this.all_accounts = data.all_accounts;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount(); this.getUser();
                this.loadSite();
                this.$refs.filter.hide();
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNew").modal("show");
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            create() {
                this.form.post("/api/ledger")
                .then((data) => {
                    $("#addNew").modal("hide");
                    console.log(data)
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
                            "Ledger Created Successfully.",
                            "success"
                        );
                    }
                    this.loadAccount(); this.getUser();
                    this.loadSite();
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.accounts = data.accounts;
                });
            },
        }
    };
</script>

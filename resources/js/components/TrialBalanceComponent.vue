<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-12">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal class="float-right">
                        <i class="fa fa-filter"></i> Filter
                    </b-button>
                    <b-modal id="filter-modal" ref="filter" title="Select Date" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label-for="Date">
                                <b-form-datepicker v-model="filterForm.end_date" placeholder="Select date"
                                    :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                </b-form-datepicker>
                            </b-form-group>

                            <b-button type="submit" variant="primary">Search</b-button>
                        </b-form>
                    </b-modal>
                </div>
            </div>

       
            <div class="card">
                <div class="text-center p-2">
                    <h4><strong>{{ site.sitename }} - Trial Balance as at {{ filterForm.end_date | myDate }}</strong></h4>
                </div>

                <div class="card-body table-responsive p-0" v-if="accounts.length>0">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Particular</th>
                                <th>Debit</th>
                                <th>Credit</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(account, index) in accounts">
                                <td>{{ index + 1}}</td>

                                <td>{{ account.account_name }}</td>
                                <td>
                                    <span v-if="account.type=='DP' || account.type=='DB'">
                                        <span v-html="nairaSign"></span>{{ formatPrice(account.amount) }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="account.type=='CP' || account.type=='CB'">
                                        <span v-html="nairaSign"></span>{{ formatPrice(account.amount) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr>
                                <td></td>
                                <td><b>Total</b></td>
                                <td>
                                    <b><span v-html="nairaSign"></span>{{ formatPrice(total_debit) }}</b>
                                </td>
                                <td>
                                    <b><span v-html="nairaSign"></span>{{ formatPrice(total_credit) }}</b>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div v-else class="text-center alert alert-danger p-3">
                    No Transaction
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
            this.loadSite();
        },

        data() {
            return {
                filterForm: {
                    //start_date: moment().subtract(30, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                    selected: '10',
                },
                nairaSign: "&#x20A6;",
                accounts: [],
                grouped_ledgers: [],
                editMode: false,
                is_busy: false,
                total_credit: '',
                total_debit: '',
                site: '', 
                numbers: [
                    { value: '10', text: '10' },
                    { value: '15', text: '15' },
                    { value: '20', text: '20' },
                    { value: '30', text: '30' },
                ],

                accounts: { 
                    data: '', 
                },
            };
        },

        methods: {
            getResults(page = 1) {
                axios.get("/api/account/trialbalance?page=" + page)
                .then(response => {
                    this.accounts = response.data.accounts;
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },
            
            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/account/trialbalance", { params: this.filterForm })
                .then(({ data }) => {
                    this.accounts = data.accounts;
                    this.total_credit = data.total_income;
                    this.total_debit = data.total_expense;

                    console.log(this.accounts)
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.$refs.filter.hide();
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadAccount();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
        }
    };
</script>

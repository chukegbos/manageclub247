<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-12">
                    <b-button variant="outline-primary" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>
                    <b-modal id="filter-modal" ref="filter" title="Select Date" hide-footer>
                        <b-form @submit.stop.prevent="onFilterSubmit">
                            <b-form-group label="Date:" label-for="End Date">
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
                    <h4><strong>{{ site.sitename }} - A statement of profit or loss as at {{ filterForm.end_date | myDate }}</strong></h4>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><h5><b>Income</b></h5></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="income in incomes">
                                <td>- {{ income.account_name }}</td>
                                <td>
                                    <span v-html="nairaSign"></span>{{ formatPrice(income.amount) }}
                                </td>
                                <td>
                                    
                                </td>
                            </tr>

                            <tr>
                                <td><b>Total Income</b></td>
                                <td>
                                    
                                </td>
                                <td>
                                    <b><span v-html="nairaSign"></span>{{ formatPrice(total_income) }}</b>
                                </td>

                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>

                        <thead>
                            <tr>
                                <th><h5><b>Expenses</b></h5></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="expense in expenses">
                                <td>- {{ expense.account_name }}</td>
                                <td>
                                     <span v-html="nairaSign"></span>{{ formatPrice(expense.amount) }}
                                </td>
                                <td>
                                    
                                </td>
                            </tr>

                            <tr>
                                <td><b>Total Expenses</b></td>
                                <td>
                                    
                                </td>
                                <td>
                                    <b><span v-html="nairaSign"></span>{{ formatPrice(total_expense) }}</b>
                                </td>
                            </tr>
                        </tbody>

                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td><b>Profit</b></td>
                                <td>
                                    
                                </td>
                                <td>
                                    <b><span v-html="nairaSign"></span>{{ formatPrice(profit) }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                    end_date: moment().subtract(1, 'days').format("YYYY-MM-DD"),
                },
                nairaSign: "&#x20A6;",
                incomes: [],
                expenses: [],
                total_income: '',
                total_expense: '',
                profit: '',
                editMode: false,
                is_busy: false,
                site: '',
            };
        },

        methods: {
            loadAccount() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/account/profitloss", { params: this.filterForm })
                .then(({ data }) => {
                    this.incomes = data.income;
                    this.expenses = data.expense;
                    this.total_income = data.total_income;
                    this.total_expense = data.total_expense;
                    this.profit = data.profit;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            loadSite() {
                axios.get("/api/setting")
                .then(({ data }) => {
                    this.site = data;
                });
            },

            onFilterSubmit()
            {
                this.loadAccount();
                this.$refs.filter.hide();
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
        }
    };
</script>

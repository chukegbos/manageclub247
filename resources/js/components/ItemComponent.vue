<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-12">
                    <h4 class="text-center"><strong>Item report sold as at {{ filterForm.start_date | myDate }} to {{ filterForm.end_date | myDate }}</strong></h4>
                </div>

                <div class="col-md-12">
                    <b-button variant="outline-primary" class="pull-right m-2" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                    <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
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

                            <div class="form-group">
                                <label>Front Desk</label>
                                <b-form-select
                                    v-model="filterForm.frontdesk_id"
                                    :options="staff"
                                    value-field="id"
                                    text-field="name"
                                >
                                <template v-slot:first>
                                    <b-form-select-option :value="0">
                                        All
                                    </b-form-select-option>
                                </template>
                                </b-form-select>
                            </div>

                            <b-form-group label="Select Steward:" label-for="staff">
                                <b-form-select
                                    v-model="filterForm.steward_id"
                                    :options="staff"
                                    value-field="id"
                                    text-field="name">

                                    <template v-slot:first>
                                        <b-form-select-option :value="0">
                                            All
                                        </b-form-select-option>
                                    </template>
                                </b-form-select>
                            </b-form-group>

                            <div class="form-group">
                                <label>Bar</label>
                                <b-form-select
                                    v-model="filterForm.store_id"
                                    :options="stores"
                                    value-field="id"
                                    text-field="name"
                                >
                                <template v-slot:first>
                                    <b-form-select-option :value="0">
                                        All
                                    </b-form-select-option>
                                </template>
                                </b-form-select>
                            </div>

                            <div class="form-group">
                                <label>Kitchen</label>
                                <b-form-select
                                    v-model="filterForm.kitchen_id"
                                    :options="kitchens"
                                    value-field="id"
                                    text-field="name"
                                >
                                <template v-slot:first>
                                    <b-form-select-option :value="0">
                                        All
                                    </b-form-select-option>
                                </template>
                                </b-form-select>
                            </div>
                            <b-button type="submit" variant="primary">Filter</b-button>
                        </b-form>
                    </b-modal>                
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            Total Product: <b>{{ report_items.data.length }}</b>
                        </div>

                        <div class="col-md-2">
                            Total Amount: <b><span v-html="nairaSign"></span>{{ formatPrice(this.totalPrice) }}</b>
                        </div>

                        <div class="col-md-2">
                            Front Desk: <b>{{ frontdesk }}</b>
                        </div>

                        <div class="col-md-2">
                            Steward: <b>{{ steward }}</b>
                        </div>

                        <div class="col-md-2">
                            Bar: <b>{{ store }}</b>
                        </div>

                        <div class="col-md-2">
                            Kitchen: <b>{{ kitchen }}</b>
                        </div>

                        <!--<div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="report_items" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>-->
                    </div>
                </div>

                <div class="card-body table-responsive p-0" v-if="report_items.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Amount (<span v-html="nairaSign"></span>)</th>
                        </tr>

                        <tr v-for="order in report_items.data" :key="order.id">
                            <td>{{ order.product_name }}</td>
                            <td>{{ order.qty }}</td>
                            <td>{{ formatPrice(order.totalPrice)  }}</td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Transaction Found.</strong></h3></div>
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
                    start_date: moment().subtract(130, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                    customer: '',
                    store_id: 0,
                    kitchen_id: 0,
                    selected: '100',
                    steward_id: 0,
                    frontdesk_id: 0,
                },
                action: {
                    selected: []
                },
                stores: [],
                kitchens: [],
                unprintable: false,
                count_all: '',
                nairaSign: "&#x20A6;",
                staff: {},
                user: "",
                is_busy: false,
                report_items: {},
                report_items: {
                    data: '',
                },
                totalPrice: '',
                frontdesk: '',
                steward: '',
                kitchen: '',
                store: '',
            };
        },

        methods: {
            getSdID(data){
                this.filterForm.steward_id = data.id;
            },

            getFdID(data){
                this.filterForm.frontdesk_id = data.id;
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadSales();
            },

            setSelected(value) {
                this.filterForm.store = value.id;
            },

            setKSelected(value) {
                this.filterForm.kitchen = value.id;
            },

            getResults(page = 1) {
                axios.get('/api/store/item?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.report_items = response.data.report_data;
                })
                .catch((err) => {
                        console.log(err);
                });
            },

            onPrint() {
                this.$htmlToPaper('printMe');
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;
                axios.get('/api/store/item', { params: this.filterForm })
                .then((data) => {
                    console.log(data.data)
                    if(this.filterForm.selected==0)
                    {
                        this.report_items.data = data.data.report_data;
                    }
                    else{
                        this.report_items = data.data.report_data;
                    }

                    this.totalPrice = this.report_items.data.reduce((acc, item) => acc + item.totalPrice, 0);
                    this.frontdesk = data.data.frontdesk;
                    this.steward = data.data.steward;
                    this.staff = data.data.users;
                    this.stores = data.data.stores;
                    this.kitchens = data.data.kitchens;
                    this.store = data.data.store;
                    this.kitchen = data.data.kitchen;
                })

                .catch((err) => {
                    console.log(err);
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

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }

    .sell{
        color: #fff ! important;
    }
</style>

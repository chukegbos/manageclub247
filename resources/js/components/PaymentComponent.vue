<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-12">
                    <h4 class="text-center"><strong>Payment history as at {{ filterForm.start_date | myDate }} to {{ filterForm.end_date | myDate }} as recorded by {{ frontdesk }}</strong></h4>
                </div>
            </div>

            <div class="card">
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-3">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                        </div>
                        <div class="col-md-3">
                             Total: <b>{{ count_all }} Payment Debits</b>
                        </div>
                        <div class="col-md-3">
                            Total Amount: <b><span v-html="nairaSign"></span>{{ formatPrice(this.totalPrice) }}</b>
                        </div>

                        <div class="col-md-3">
                            Front Desk: <b>{{ frontdesk }}</b>
                        </div>
                        <div class="col-md-6">
                            <pagination :data="debits" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-6">
                            <b-button variant="outline-primary" class="pull-right m-2" size="sm" v-b-modal.filter-modal><i class="fa fa-filter"></i> Filter</b-button>

                            <b-modal id="filter-modal" ref="filter" title="Report Filter" hide-footer>
                                <b-form @submit.stop.prevent="onFilterSubmit">
                                    <b-form-group label="Start Date:" label-for="Start Date">
                                        <b-form-datepicker v-model="filterForm.start_date" placeholder="Start date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>
                                    
                                    <b-form-group label="End Date:" label-for="End Date">
                                        <b-form-datepicker v-model="filterForm.end_date" placeholder="End date"
                                            :date-format-options="{ year: 'numeric', month: 'short', day: '2-digit', weekday: 'short' }">
                                        </b-form-datepicker>
                                    </b-form-group>

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

                                    <div class="form-group">
                                        <label>Random Search</label>
                                        <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search"></b-form-input>
                                    </div>
                                    <b-button type="submit" variant="primary">Filter</b-button>
                                </b-form>
                            </b-modal>                
                        </div>
                    </div>
                </div>

                <div class="card-body table-responsive p-0" v-if="debits.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Receipt No</th>
                                <th>Member</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Date Created</th>
                                <th>Created By</th>
                                <th>Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debits.data" :key="debt.id">
                                <td>{{ debt.rec_id }}</td>
                                <td>{{ debt.get_member.get_member }}</td>
                                <td>{{ debt.get_product.description }}</td>
                                <td><span v-html="nairaSign"></span>{{ formatPrice(debt.amount)  }}</td>
                                <td>{{ debt.created_at | myDate }}</td> 
                                <td>{{ debt.created_by }}</td>
                                <td>
                                    {{ debt.payment_channel }}<br>
                                    <a href="javascript:void(0)" @click="viewReceipt(debt)">Receipt</a>
                                </td> 
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Payment History Found.</strong></h3></div>
                </div>
                
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import moment from 'moment';

    export default {
        created() {
            this.loadDebits();
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                model: {},
                debits: {},
                debit: "",
                frontdesk: '',
                staff: {},
                totalPrice: '',
                form: new Form({
                    id: "",
                    title: "",
                }),
                nairaSign: "&#x20A6;",
                filterForm: {
                    name: '',
                    selected: 50,
                    frontdesk_id: 0,
                    start_date: moment().subtract(130, 'days').format("YYYY-MM-DD"),
                    end_date: moment().format("YYYY-MM-DD"),
                },
                action: {
                    selected: []
                },
                debits: {
                    data: {},
                },

                extend: new Form({
                    id: '',
                    grace_period: '',
                }),

                count_all: '',
                unprintable: false,
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadDebits();
            },

            loadDebits() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/payment", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.debits.data = data.payements;
                    }
                    else{
                        this.debits = data.payments;
                    }
                    this.totalPrice = this.debits.data.reduce((acc, item) => acc + item.amount, 0);
                    this.frontdesk = data.frontdesk;
                    this.staff = data.users;
                    this.count_all = data.all;
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            newModal() {
                (this.editMode = false), this.form.reset();
                $("#addNewdebit").modal("show");
            },

            extendPeriod(debt) {
                this.extend.id = debt.id;
                this.extend.grace_period = debt.grace_period;
                $("#extend").modal("show");
            },

            updateExtend() {
                axios.post("/api/payment/graceperiod", this.extend)

                .then((data) => {
                    $("#extend").modal("hide");
                    Swal.fire("Updated!", "Grace Period Extended Successfully.", "success");
                    this.loadDebits();
                })

                .catch();
            },
            
            getResults(page = 1) {
                axios.get("/api/payment/debits?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.debits = response.data.debits;
                });
            },

            editModal(debit) {
                (this.editMode = true), this.form.reset();
                $("#addNewdebit").modal("show");
                this.form.fill(debit);
            },

            startDateMoment(value, grace_period) {
                return moment(value).add(grace_period, 'days').format('MMMM Do YYYY');
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            onFilterSubmit()
            {
                this.loadDebits();
                this.$refs.filter.hide();
            },

            createdebit() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewdebit").modal("hide");
                this.form.post("/api/payment/debits")
                .then(() => {
                    Swal.fire(
                        "Created!",
                        "Payment debit Created Successfully.",
                        "success"
                    );
                    this.loadDebits(); 
                })
                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    
                    this.is_busy = false;
                    this.loadDebits(); 
                });
            },

            updatedebit() {
                if (this.is_busy) return;
                this.is_busy = true;
                $("#addNewdebit").modal("hide");
                this.form.put("/api/payment/debits/" + this.form.id)
                .then(() => {
                    Swal.fire("Updated!", "Payment debit Updated Successfully.", "success");
                    this.loadDebits(); 
                })

                .catch(() => {
                    Swal.fire(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                })
                .finally(() => {
                    this.is_busy = false;
                    this.loadDebits(); 
                });
            },

            viewReceipt(debt) {
                this.$router.push({ path: '/payment/reciept/' + debt.id})
            },

            onDeleteAll(id) {
                if(id){
                    this.action.selected.push(id);
                }
                
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, delete it!"
                })
                .then(result => {
                    if (result.value) {
                        if (this.is_busy) return;
                        this.is_busy = true;
                        axios.get('/api/payment/debits/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Payment debit(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadDebits();
                        })

                        .catch(() => {
                            Swal.fire(
                                "Failed!",
                                "Ops, Something went wrong, try again.",
                                "warning"
                            );
                            this.is_busy = false;
                        });
                    }
                });
            },
        },

        computed: {
            selectAll: {
                get: function () {
                    return this.debits.data ? this.action.selected.length == this.debits.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.debits.data.forEach(function (debit) {
                            selected.push(debit.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>

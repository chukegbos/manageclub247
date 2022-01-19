<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Payment History</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search"></b-form-input>

                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                        
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="debits.data.length>0" id="printMe">
                    <table class="table table-hover">
                        <thead>
                            <tr>
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
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Payment Debits</b>
                        </div>

                        <div class="col-md-4" v-if="this.filterForm.selected!=0">
                            <pagination :data="debits" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>
                    </div>
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
                form: new Form({
                    id: "",
                    title: "",
                }),
                nairaSign: "&#x20A6;",
                filterForm: {
                    name: '',
                    selected: '10',
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
                    console.log(this.debits)
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

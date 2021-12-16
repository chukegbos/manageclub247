<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Payment Debits</strong></h2>
                </div>

                <div class="col-md-6">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2">
                        Add Debit
                    </b-button>
               
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-input id="name" v-model="filterForm.name" type="text" placeholder="Search debit"></b-form-input>

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
                                <th><input type="checkbox" v-model="selectAll"></th>
                                <th>Member</th>
                                <th>Payment</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Date Created</th>
                                <th>Grace Period</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="debt in debits.data" :key="debt.id">
                                <td> <input type="checkbox" v-model="action.selected" :value="debt.id" number></td>
                                <td>{{ debt.last_name }} {{ debt.first_name }} {{ debt.middle_name }}</td>
                                <td><span v-if="debt.product">{{ debt.product.payment_name }}</span></td>
                                <td>{{ debt.description }}</td>
                                <td>
                                    <span v-html="nairaSign"></span>{{ formatPrice(debt.amount)  }}
                                    <br> <span class="badge badge-danger btn-sm">Unpaid</span>
                                </td>
                                <td>{{ debt.start_date | myDate }}</td> 
                                <td>
                                    {{ debt.grace_period }} Days 
                                    <span class="badge badge-danger" v-if="debt.period==0">Expired</span><br>
                                    {{ startDateMoment(debt.start_date, debt.grace_period) }}
                                </td> 
                                
                                <td>
                                    <a href="javascript:void(0)" @click="extendPeriod(debt)" class="btn btn-info btn-sm">Extend Period</a> 

                                    <a href="javascript:void(0)" @click="onPay(debt)" class="btn btn-success btn-sm">Pay</a>
                                   
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Payment Debit Found.</strong></h3></div>
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

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal fade" id="extend" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Increase Grace Period
                            </h5>

                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <form @submit.prevent="updateExtend()">
                            <div class="modal-body">
                               
                                <div class="form-group">
                                    <label>How many more days do you want to add?</label>
                                    <input v-model="extend.grace_period" type="number" class="form-control"/>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">
                                    Close
                                </button>

                                <button type="submit" class="btn btn-success">
                                    Increase
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addNewdebit" tabindex="-1" role="dialog" aria-labelledby="addNewdebitLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5
                                class="modal-title"
                                id="addNewdebitLabel"
                                v-show="!editMode"
                            >
                                Add New debit
                            </h5>
                            <h5
                                class="modal-title"
                                id="addNewdebitLabel"
                                v-show="editMode"
                            >
                                Update debit
                            </h5>
                            <button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form @submit.prevent="editMode ? updatedebit() : createdebit()">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Name<span class="text-danger pulll-right">*</span></label>
                                    <input v-model="form.title" type="text" name="name" required class="form-control" :class="{'is-invalid': form.errors.has('title')}"/>
                                    <has-error :form="form" field="title"></has-error>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-danger"
                                    data-dismiss="modal"
                                >
                                    Close
                                </button>
                                <button
                                    v-show="editMode"
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    Update
                                </button>
                                <button
                                    v-show="!editMode"
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Create
                                </button>
                            </div>
                        </form>
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

                axios.get("/api/payment/debits", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.debits.data = data.debits;
                    }
                    else{
                        this.debits = data.debits;
                    }
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

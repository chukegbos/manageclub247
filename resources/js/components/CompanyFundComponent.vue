<template>
    <b-overlay :show="is_busy" rounded="sm">

        <div class="container-fluid">
            <div class="mb-2 p-2">
                <h6 class="text-center"><strong>Fund Company's Account</strong></h6>
            </div>
     
            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="create()">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input v-model="form.ledger_date" type="date"
                                        name="date" class="form-control" :class="{'is-invalid': form.errors.has('ledger_date')}"/>
                                    <has-error :form="form" field="ledger_date"></has-error>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input v-model="form.amount" type="number"
                                        name="amount" class="form-control" :class="{'is-invalid': form.errors.has('amount')}"/>
                                    <has-error :form="form" field="amount"></has-error>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea v-model="form.description" class="form-control" :class="{'is-invalid': form.errors.has('description')}" ></textarea>
                                    <has-error :form="form" field="description"></has-error>
                                </div>
                            </div>

                            

                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    Fund
                                </button>
                            </div>
                        </div>
                    </form>
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
        },

        data() {
            return {
                is_busy: false,
                form: new Form({
                    id: "",
                    ledger_date: '',
                    account: 5,
                    amount: '',
                    description: '',
                }),
            };
        },

        methods: {
            create() {
                if (this.is_busy) return;
                this.is_busy = true;

                this.form.post("/api/ledger")
                .then((data) => {
                    $("#addNew").modal("hide");
                
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
                    this.form.account = 5;
                    this.form.id = "",
                    this.form.ledger_date = '';
                    this.form.amount = '';
                    this.form.description = '';
                    this.$router.push({ path: "/account/ledger" });
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },
        }
    };
</script>

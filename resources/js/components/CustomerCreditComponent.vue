<template>
    <b-overlay :show="is_busy" rounded="sm">   
        <div class="container-fluid">
            <div class="row mb-2 p-1">
                <div class="col-md-12">
                    <h2><strong>Adjust Credit Level</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="updateCredit()">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Customer</label>
                                <vue-typeahead-bootstrap
                                    v-model="userQuery"
                                    :ieCloseFix="false"
                                    :data="users"
                                    :serializer="data => data.name"
                                    @hit="getUserID($event)"
                                    placeholder="Search for customer"
                                    @input="lookUser"
                                />
                            </div>

                           
                            <div class="form-group">
                                <label>Customer Credit Unit</label>
                                <input v-model="credit.credit_unit" type="number" class="form-control"/>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                Close
                            </button>

                            <button type="submit" class="btn btn-success">
                                Adjust
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import {debounce} from 'lodash';
    export default {
        data() {
            return {
                is_busy: false,
                userQuery: '',
                credit: new Form({
                    payer_id: '',
                    credit_unit: '',
                    unique_id: '',
                }),

                users: [],
            };
        },

        created() {
    
        },

        methods: {
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
                this.credit.payer_id = data.id;
                this.credit.unique_id = data.unique_id;
                this.credit.credit_unit = data.credit_unit
            },

            updateCredit() {


                if(this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/user/credituser", this.credit)

                .then((data) => {
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "warning"
                        );
                    }
                    else{
                        Swal.fire("Updated!", "Customer Credit Level Adjusted Successfully.", "success");
                        this.$router.push({ path: "/admin/customers/" + this.credit.unique_id });
                    }
                })

                .catch();
                this.is_busy = false;
            },
         
        },
    };
</script>

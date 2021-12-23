<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid mt-2">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>{{ user.name }} </strong></h2>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <b-row>
                        <b-col cols="12" md="6">
                            <table class="table table-hover">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ user.name }}</td>
                                </tr>

                                <tr>
                                    <th>Bar</th>
                                    <td>{{ user.store }}</td>
                                </tr>

                                <tr>
                                    <th>Role</th>
                                    <td>
                                        <span v-if="user.role==8">
                                            <span v-if="user.invoice==1">Bar Front desk officer</span>
                                            <span v-else>{{ user.get_role.title }}</span>

                                        </span>
                                        <span v-else>{{ user.get_role.title }}</span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Phone</th>
                                    <td>{{ user.phone }}</td>
                                </tr>

                                <tr>
                                    <th>Email</th>
                                    <td>{{ user.email }}</td>
                                </tr>

                                <tr>
                                    <th>Address</th>
                                    <td>{{ user.address }}</td>
                                </tr>

                                <tr>
                                    <th>Next of Kin</th>
                                    <td>{{ user.next_of_kin }}</td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Phone</th>
                                    <td>{{ user.next_of_kin_phone }}</td>
                                </tr>
                                <tr>
                                    <th>Next of Kin Address</th>
                                    <td>{{ user.next_of_kin_address }}</td>
                                </tr>
                            </table>
                        </b-col>
                    </b-row>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
    import {debounce} from 'lodash';
    export default {
        data(){
            return {
                is_busy: false,
                user: '',
                users: [],
                userQuery: '',
                user: {
                    get_role: '',
                }
            }
        },

        created(){
            this.loadPage();
        },

        methods: {
            loadPage(){
                if(this.is_busy) return;
                this.is_busy = true;

                let unique_id = this.$route.params.id;
                axios.get('/api/user/' + unique_id)
                .then((response) => {
                    if(response.data.error){
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/staff"});
                    }
                    else {
                        this.user = response.data;
                        console.log(response)
                    }
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },

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

                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/user/' + data.unique_id)
                .then((response) => {
                    if(response.data.error){
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                        this.$router.push({ path: "/admin/staff"});
                    }
                    else {
                        this.user = response.data;
                        console.log(response)
                    }
                })

                .catch((err) => {
                    console.log(err);
                })

                .finally(() => {
                    this.is_busy = false;
                });
            },
        },
        
    }
</script>

<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">  
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Messages</strong></h2>
                </div>

                <div class="col-md-8">
                    <b-button variant="outline-primary" size="sm" @click="newModal" class="pull-right m-2" v-if="admin.role==1 || admin.role==6 || admin.role==7">
                        Compose
                    </b-button>                      
                </div>
            </div>

            <div class="card">
                <div class="card-body" v-if="messages.data.length>0" id="printMe">
                    <table class="table table-striped table-responsive-md">
                        <thead>
                        <tr>
                            <th>Message</th>
                            <th>Date Created</th>
                            <th>Creator</th>
                            <!--<th>Action</th>-->
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(mes, index) in messages.data" :key="index">
                            <td>{{ mes.message }}</td>
                            <td>{{ mes.created_at | myDate }}</td>

                            <td>
                                {{ mes.user_id }}
                            </td>
                            <!--<td>
                                <b-dropdown size="sm" right text="Action" class="m-2">
                                    <b-dropdown-item-button v-if="(message.getters['auth/has_permission']('delete_campaign')) && (message.status!=3 || message.status!=4)" @click="onDelete(message)">Delete</b-dropdown-item-button>

                                    <b-dropdown-item-button @click="onViewMessage(message)">View Message</b-dropdown-item-button>

                                    <b-dropdown-item-button @click="onView(message)">View Delivery Log</b-dropdown-item-button>
                                </b-dropdown>
                            </td>-->
                        </tr>
                        </tbody>
                    </table>


                </div>
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Message Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>
                                Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Messages</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="messages" @pagination-change-page="getResults" :limit="-1"></pagination>
                        </div>

                        <div class="col-md-2">
                            <b-button variant="outline-danger" size="sm" v-if="action.selected.length" class="pull-right" @click="onDeleteAll"><i class="fa fa-trash"></i> Delete Selected</b-button>

                            <b-button disabled size="sm" variant="outline-danger" v-else class="pull-right"> <i class="fa fa-trash"></i> Delete Selected</b-button>
                        </div>
                    </div>
                </div>
            </div>
            
            
        </div>
    </b-overlay>
</template>

<script>
    export default {
        created() {
            this.getUser();
            this.loadMessages();
            
        },

        data() {
            return {
                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                messages: {},

                messages: {
                    data: {},
                },
                model: {},
                filterForm: {
                    selected: '10',
                    status: '',
                },
                is_busy: false,
                action: {
                    selected: []
                },
                formData: {
                    quantity: '',
                    amount: '',
                    unit: '',
                },

                status_options: [
                    { value: '', text: 'All' },
                    { value: '0', text: 'Draft' },
                    { value: '1', text: 'Postponed' },
                    { value: '2', text: 'In Progress' },
                    { value: '3', text: 'Completed' },
                ],
                count_all: '',
                sms_units: '',
                admin: '',
            };
        },

        methods: {
            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadMessages();
                this.getUser();
            },

            loadMessages() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.get("/api/messages", { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.messages.data = data.messages;
                    }
                    else{
                        this.messages = data.messages;
                    }
                    console.log(this.messages)
                    this.count_all = data.all;
                    this.sms_units = data.sms_units;
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
                this.$router.push({path: `/messages/compose`});
            },

            getUser() {
                axios.get("/api/user").then(({ data }) => {
                    this.admin = data.user;
                });
            },

            getResults(page = 1) {
                axios.get("/api/messages?page=" + page, { params: this.filterForm })
                .then(response => {
                    this.messages = response.data.messages;
                });
            },


            onFilterSubmit()
            {
                this.loadMessages();
                this.getUser();
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
                        axios.get('/api/messages/delete', { params: this.action})
                        .then(() => {
                            Swal.fire(
                                "Deleted!",
                                "Message(s) deleted.",
                                "success"
                            );
                            this.is_busy = false;
                            this.loadMessages();
                            this.getUser();
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
                    return this.messages.data ? this.action.selected.length == this.messages.data.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.messages.data.forEach(function (message) {
                            selected.push(message.id);
                        });
                    }

                    this.action.selected = selected;
                }
            }
        },
    };
</script>

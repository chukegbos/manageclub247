<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h2><strong>Send SMS</strong></h2>
                </div>
            </div>

            <b-form @submit.stop.prevent="onFormSubmit($event)">
                <div class="card card-danger">
                    <div class="card-body row">
                        <!--<b-form-group label="Sender:" class="col-md-6">
                            <b-form-input v-model="formData.sender" type="text" ></b-form-input>
                            <div v-if="errors.sender"><small class="text-danger">{{ errors.sender }}</small></div>
                        </b-form-group>-->
                        <b-form-group label="Message Type:" class="col-md-6">
                            <select v-model="formData.message_type" class="form-control">
                                <option v-for="message_type in message_types" v-bind:value="message_type.value">
                                    {{ message_type.text }}
                                </option>
                            </select>
                        </b-form-group>

                        <b-form-group label="People to send:" class="col-md-6">
                            <select v-model="formData.people" class="form-control">
                                <option v-for="people in peoples" v-bind:value="people.value">
                                    {{ people.text }}
                                </option>
                            </select>
                        </b-form-group>

                        <b-form-group label="Phone Numbers:" v-if="formData.people==0" class="col-md-12">
                            <small class="text-info">Separate numbers with a comma</small>
                            <b-form-input v-model="formData.phone" type="text" @input="countPhone()"></b-form-input>
                            <small>
                                {{ formData.phone_count }} 
                            </small>

                            <div v-if="errors.phone">
                                <small class="text-danger">{{ errors.phone }}</small>
                            </div>
                        </b-form-group>
                        
                        

                        <b-form-group label="Message:" class="col-md-12" v-if="formData.message_type==0">
                            <textarea  class="form-control" v-model="formData.message" @input="update()"></textarea>

                            <small>
                                Character(s): {{ formData.characters }}<br>
                                Page(s): {{ formData.pages }}
                            </small>
                            <div v-if="errors.message"><small class="text-danger">{{ errors.message }}</small></div>
                        </b-form-group>
                    </div>
                </div>
       
                <b-button type="submit" variant="primary">Send</b-button>
            </b-form>
        </div>
    </b-overlay>
</template>

<script>
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';

    export default {
        data() {
            return {
                is_busy: false,
                editMode: false,
              
                defer_time: '00:00:00',
                formData: new Form({
                    message: '',
                    phone: '',
                  
                    group_count: '',
                    characters: '',
                    pages: 1,
                    phone_count: 0,
                    group: 0,
                    defer_date: '',
                    people: 1,
                    message_type: 1,
                }),

                is_busy: false,
                errors: '',
                merchant_unit: '',
                peoples: [
                    { text: 'All Members', value: 1 },
                ],

                message_types: [
                    { text: 'Debt SMS', value: 1 },
                    { text: 'Custom SMS', value: 0 },
                ],
            };
        },

        created() {
            this.getUser();
        },

        components: {
            VueBootstrap4Table
        },

        methods: {
            getUser() {
                axios.get("/api/user")
                .then(({ data }) => {
                    this.stores = data.stores;
                    this.user = data.user;
                });
            },

            onFormSubmit($event)
            {
                var info = '';
                var total = '';
                var phone_total = '';

                info = 'Are you sure you want to proceed?';

                if(confirm(info))
                {
                    if(this.is_busy) return;
                    this.is_busy = true;
                    this.formData.post("/api/messages")
                    .then(() => {
                        Swal.fire(
                            "Created!",
                            "Message Processing.",
                            "success"
                        );
                        this.$router.push({ path: '/messages'});
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
                }
            },

            update(){
                this.formData.characters = this.formData.message.length;   
                var x = (this.formData.characters/161);
                var myTrunc = Math.trunc( x );
                this.formData.pages = Number(1 + myTrunc)          
            },

            countPhone(){
                var count_phone = this.formData.phone.split(',');
                this.formData.phone_count = count_phone.length;             
            },

            onSelect(id) {
                return axios.get(BASE_URL + `api/messages/countgroup/`+ id)
                .then((data) => {
                    this.formData.group_count = data.data;
                });
            }

        },
    };
</script>

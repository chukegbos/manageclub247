<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">           
            <div class="mb-2 p-2 text-center">
                <h4>
                    <strong v-show="!editMode">New Member</strong>
                    <strong v-show="editMode">Update Member</strong>
                </h4>
            </div>

            <form-wizard @on-complete="editMode ? updateUser() : createUser()">
                <tab-content title="Login Detail" icon="fa fa-user">
                    <b-card no-body>
                        <b-card-body>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>First Name</label>
                                    <input v-model="form.first_name" type="text" required class="form-control"/>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Middle Name</label>
                                    <input v-model="form.middle_name" type="text" class="form-control"/>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Last Name</label>
                                    <input v-model="form.last_name" type="text" required class="form-control"/>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Membership ID</label>
                                    <input v-model="form.membership_id" type="text" required class="form-control"/>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Entrance Date</label>
                                    <input v-model="form.entrance_date" type="date" required class="form-control"/>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Member's Types</label>
                                    <select v-model="form.member_type" class="form-control" required>
                                        <option value=null> -- Select Type-- </option>
                                        <option v-for="option in member_types" v-bind:value="option.id">
                                            {{ option.title }}
                                        </option>
                                    </select>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Photo Image</label>
                                        <input type="file" @change="uploadImage" accept="image/*" name="image" class="form-control">
                                    </div>
                                </div>


                                <div class="col-md-3 form-group">
                                    <label>Date of Birth</label>
                                    <input v-model="form.dob" type="date" class="form-control"/>
                                </div>

                                <div class="col-md-3">
                                    <b-form-group class="">
                                        <label>Gender</label>
                                        <b-form-select v-model="form.gender" :options="gender" required></b-form-select>
                                    </b-form-group>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Email</label>
                                    <input v-model="form.email" type="email" required class="form-control">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Phone Number(Home)</label>
                                    <input v-model="form.phone_1" type="tel" required class="form-control">
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>Phone Number (Alt)</label>
                                    <input v-model="form.phone_2" type="tel" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <b-form-group class="">
                                      <label>State of Origin</label>
                                      <select v-model="form.state_of_origin" class="form-control" @change="onChange($event)" required>
                                      <option value=null> -- Select State -- </option>
                                        <option v-for="option in states" v-bind:value="option.id">
                                          {{ option.title }}
                                        </option>
                                      </select>
                                    </b-form-group>
                                </div>

                                <div class="col-md-3">
                                    <b-form-group class="">
                                      <label>LGA of Origin</label>
                                      <select v-model="form.lga" class="form-control" required>
                                      <option value=null> -- Select LGA -- </option>
                                        <option v-for="option in lgas" v-bind:value="option.id">
                                          {{ option.name }}
                                        </option>
                                      </select>
                                    </b-form-group>
                                  </div>  

                               
                                <div class="col-md-3 form-group">
                                    <label>Home Town</label>
                                    <input v-model="form.home_town" type="text" required class="form-control"/>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                      <label>Country of Residence</label>
                                      <input v-model="form.country" type="text" class="form-control" required>
                                    </div>
                                </div>
                                

                                <div class="col-md-3">
                                    <b-form-group class="">
                                      <label>State of Residence</label>
                                      <select v-model="form.state" class="form-control" required @change="onChangeState($event)">
                                      <option value=null> -- Select State -- </option>
                                        <option v-for="option in states" v-bind:value="option.id">
                                          {{ option.title }}
                                        </option>
                                      </select>
                                    </b-form-group>
                                </div>

                                <div class="col-md-3 form-group">
                                    <label>City</label>
                                    <input v-model="form.city_resident" type="text" required class="form-control"/>
                                </div>

                                <!--<div class="col-md-3">
                                    <b-form-group class="">
                                      <label>City of Residence</label>
                                      <select v-model="form.city" class="form-control" required>
                                        <option value=null> -- Select LGA -- </option>
                                        <option v-for="option in lgah" v-bind:value="option.id">
                                          {{ option.name }}
                                        </option>
                                      </select>
                                    </b-form-group>
                                </div>-->

                                <div class="col-md-6 form-group">
                                    <label>Residential Address</label>
                                    <textarea v-model="form.address" class="form-control" required></textarea>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Office Address</label>
                                    <textarea v-model="form.office_address" class="form-control"></textarea>
                                </div>
                            </div>
                        </b-card-body>
                    </b-card>
                </tab-content>

                <tab-content title="Relationship Details" icon="fas fa-user-friends">
                    <b-card no-body>
                        <b-card-body>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Marital Status</label>
                                    <b-form-select v-model="form.marital_status" :options="marital_status" required></b-form-select>
                                </div>
                            

                                <div class="col-md-6 form-group">
                                    <label>Spouse Name</label>
                                    <input v-model="form.spouse_name" type="text" class="form-control">
                                </div>

                                <div class="col-md-12 form-group">
                                    <label>Children (If any)</label>
                                    <textarea v-model="form.children" class="form-control"></textarea>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Next of Kin</label>
                                    <input v-model="form.kin_name" type="text" class="form-control" required>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Next of Kin Relationship</label>
                                    <input v-model="form.kin_relationship" type="text" class="form-control" required>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>KIN Phone Number 1</label>
                                    <input v-model="form.kin_phone_1" type="tel" required class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>KIN Phone Number (Alt)</label>
                                    <input v-model="form.kin_phone_2" type="tel" class="form-control">
                                </div>

                                <div class="col-md-12 form-group">
                                    <label>Next of Kin Address</label>
                                    <textarea v-model="form.kin_address" required class="form-control"></textarea>
                                </div>
                            </div>
                        </b-card-body>
                    </b-card>
                </tab-content>

                <tab-content title="Additional Information" icon="fas fa-people-carry">
                    <b-card no-body>
                        <b-card-body>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>First Sponsor</label>
                                    <input v-model="form.sponsor_1" type="text" class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Second Sponsor</label>
                                    <input v-model="form.sponsor_2" type="text" class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Beneficiary Name</label>
                                    <input v-model="form.beneficiary_name" type="text" required class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Beneficiary Relationship</label>
                                    <input v-model="form.beneficiary_relationship" type="text" required class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Beneficiary Phone Number 1</label>
                                    <input v-model="form.beneficiary_phone_1" type="tel" required  class="form-control">
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Beneficiary Phone Number (Alt)</label>
                                    <input v-model="form.beneficiary_phone_2" type="tel" class="form-control">
                                </div>

                                <div class="col-md-12 form-group">
                                    <label>Beneficiary Address</label>
                                    <textarea v-model="form.beneficiary_address" required class="form-control"></textarea>
                                </div>
                            </div>
                        </b-card-body>
                    </b-card>
                </tab-content>

                <tab-content title="Membership Cards" icon="fas fa-person-booth">
                    <b-card no-body>
                        <b-card-body>
                            <b-button type="button" variant="primary" @click="onAddNewCard" size="sm" class="float-right m-2">
                                Add More
                            </b-button>
                            <table class="table table-striped table-responsive-md text-center">
                                <thead>
                                    <tr>
                                        <th>Card Number</th>
                                        <th>
                                            
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(card, index) in form.card_numbers">
                                        <td>
                                            <b-form-input v-model="card.card_number" type="text" class="form-control" required></b-form-input>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" @click="onRemoveCard(form.card_numbers.indexOf(card))"><i class="fa fa-times text-red fa-2x"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-card-body>
                    </b-card>
                </tab-content>

                <tab-content title="Educational Information" icon="fa fa-book">
                    <b-card no-body>
                        <b-card-body>
                            <b-button type="button" variant="primary" @click="onAddNewService" size="sm" class="float-right m-2">
                                Add More
                            </b-button>
                            <table class="table table-striped table-responsive-md text-center">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Institution</th>
                                        <th>Degree</th>
                                        <th>
                                            
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="(edu, index) in form.educationItems">
                                        
                                        <td>
                                            <b-form-select v-model="edu.level" :options="level" required></b-form-select>
                                        </td>
                                        <td>
                                            <b-form-input v-model="edu.institution" type="text" class="form-control" required></b-form-input>
                                        </td>
                                        <td>
                                            <b-form-select v-model="edu.degree" :options="degree" required></b-form-select>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" @click="onRemoveService(form.educationItems.indexOf(edu))"><i class="fa fa-times text-red fa-2x"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-card-body>
                    </b-card>
                </tab-content>

                <tab-content title="Member Sections" icon="fa fa-check">
                    <b-card no-body>
                        <b-card-body>
                            <div class="row">
                                <div class="col-md-3" v-for="section in sections" :key="section.id">
                                    <div class="m-1">
                                        <input type="checkbox" v-model="form.sections" :value="section.id" number> {{ section.title }}
                                    </div>
                                </div>
                            </div>
                        </b-card-body>
                    </b-card>
                </tab-content>
            </form-wizard> 
        </div>
    </b-overlay>
</template>

<script>
    import axios from 'axios';
    import {debounce} from 'lodash';
    
    export default {
        created(){
            this.loadPage();
            this.loadOther();
            this.onAddNewService();
            this.onAddNewCard();
        },

        data(){
            return{
                form: new Form({
                    id: "",
                    first_name: "",
                    last_name: "",
                    middle_name: "",
                    email: "",
                    phone_1: "",
                    phone_2: "",
                    address: "",
                    state: null,
                    city: null,
                    city_resident:null,
                    office_address: '',
                    credit_unit: "",
                    c_person: "",
                    member_type: null,
                    card_numbers: [],
                    entrance_date: '',
                    image: '',
                    gender: null,
                    dob: '',
                    state_of_origin: null,
                    lga: null,
                    home_town: '',
                    country: 'Nigeria',
                    marital_status : null,
                    children: '',
                    spouse_name: '',
                    kin_name: '',
                    kin_addres: '',
                    kin_relationship: 'Father',
                    kin_phone_1: "",
                    kin_phone_2: "",
                    beneficiary_name: '',
                    beneficiary_addres: '',
                    beneficiary_relationship: 'Father',
                    beneficiary_phone_1: "",
                    beneficiary_phone_2: "",
                    sponsor_1: "",
                    sponsor_2: "",
                    educationItems: [],
                    sections: [],
                    membership_id: '',
                }),
                is_busy: false,
                editMode: false,
         
                member_types: [],
                sections: [],
                states: {},
                lgas: {},
                lgah: {},
                gender: [
                    { value: null, text: 'Select Gender' },
                    { value: 'male', text: 'Male' },
                    { value: 'female', text: 'Female' },
                ],
                marital_status: [
                    { value: null, text: '--Select--' },
                    { value: 'Single', text: 'Single' },
                    { value: 'Married', text: 'Married' },
                    { value: 'Widow', text: 'Widow' },
                    { value: 'Widower', text: 'Widower' },
                    { value: 'Divorced', text: 'Divorced' },
                ],

                level: [
                    { value: null, text: '--Select--' },
                    { value: 'High School', text: 'High School' },
                    { value: 'University', text: 'University' },
                ],

                degree: [
                    { value: null, text: '--Select--' },
                    { value: 'BSc', text: 'BSc' },
                    { value: 'MSc', text: 'MSc' },
                    { value: 'MBA', text: 'MBA' },
                    { value: 'PhD', text: 'PhD' },
                    { value: 'Others', text: 'Others' },
                ],
            }
        },

        methods: {
            onAddNewService(){
                this.form.educationItems.push(this.setServiceModel({}));  
            },

            onAddNewCard(){
                this.form.card_numbers.push(this.setCardModel({}));  
            },

            setServiceModel(model, newModel){
                model.institution = newModel !== undefined ? newModel.institution: '';
                model.level = newModel !== undefined ? newModel.level: null;
                model.degree = newModel !== undefined ? newModel.degree: null;
                return model;
            },

            setCardModel(model, newModel){
                model.card_number = newModel !== undefined ? newModel.card_number: null;
                return model;
            },

            onRemoveService(item_no)
            {
                this.form.educationItems.splice(item_no,1);
            },

            onRemoveCard(item_no)
            {
                this.form.card_numbers.splice(item_no,1);
            },

            loadPage(){
                let id = this.$route.params.id;
                if(id){
                    if(this.is_busy) return;
                    this.is_busy = true;

                    this.editMode = true;
                    axios.get('/api/customer/edit/' + id)
                    .then((response) => {
                        if(response.data.error)
                        {
                            Swal.fire(
                                "Deleted!",
                                response.data.error,
                                "error"
                            );
                            this.$router.push({ path: "/admin/customers"});
                        }
                        else{
                            this.form.id = response.data.user.id;
                            this.form.first_name= response.data.member.first_name;
                            this.form.last_name= response.data.member.last_name;
                            this.form.middle_name= response.data.member.middle_name;
                            this.form.email= response.data.user.email;
                            this.form.phone_1= response.data.member.phone_1;
                            this.form.phone_2= response.data.member.phone_2;
                            this.form.address= response.data.member.address;
                            this.form.city_resident = response.data.member.city_resident;
                            this.form.state = response.data.member.state;
                            if(this.form.state) {
                                axios.get('/api/loadLGA/'+ this.form.state) 
                                .then(({data}) => {
                                  this.lgah = data;
                                  this.form.city= response.data.member.city;
                                })
                            }
                            this.form.office_address = response.data.member.office_address;
                            this.form.member_type= response.data.member.member_type;
                            this.form.entrance_date= response.data.user.entrance_date;
                            this.form.gender= response.data.user.gender;
                            this.form.dob=response.data.user.dob;
                            this.form.state_of_origin= response.data.member.state_of_origin;
                            if(this.form.state_of_origin) {
                                axios.get('/api/loadLGA/'+ this.form.state_of_origin) 
                                .then(({data}) => {
                                  this.lgas = data;
                                  
                                })
                                this.form.lga= response.data.member.lga;
                            }
                            

                            this.form.home_town= response.data.member.home_town;
                            this.form.country = response.data.member.country;
                            this.form.marital_status = response.data.member.marital_status;
                            this.form.children= response.data.member.children;
                            this.form.spouse_name= response.data.member.spouse_name;
                            this.form.kin_name = response.data.memberAdditional.kin_name;
                            this.form.kin_address = response.data.memberAdditional.kin_address;
                            this.form.kin_relationship = response.data.memberAdditional.kin_relationship;
                            this.form.kin_phone_1= response.data.memberAdditional.kin_phone_1;
                            this.form.kin_phone_2= response.data.memberAdditional.kin_phone_1;
                            this.form.beneficiary_name= response.data.memberAdditional.beneficiary_name;
                            this.form.beneficiary_address = response.data.memberAdditional.beneficiary_address;
                            this.form.beneficiary_relationship = response.data.memberAdditional.beneficiary_relationship;
                            this.form.beneficiary_phone_1= response.data.memberAdditional.beneficiary_phone_1;
                            this.form.beneficiary_phone_2= response.data.memberAdditional.beneficiary_phone_2;
                            this.form.sponsor_1= response.data.memberAdditional.sponsor_1;
                            this.form.sponsor_2= response.data.memberAdditional.sponsor_2;
                            this.form.card_numbers = response.data.MemberCards;
                            this.form.educationItems = response.data.MemberEducation;
                            this.form.sections= response.data.MemberSection;
                            this.form.membership_id= response.data.user.unique_id;
                            console.log(response.data)
                        }
                    })

                    .catch((err) => {
                        console.log(err);
                    })
                    .finally(() => {
                        this.is_busy = false;
                    });
                }
            },

            loadOther(){
                axios.get('/api/customer/details')
                .then((response) => {
                    this.member_types = response.data.member_types;
                    this.sections = response.data.sections;
                    this.states = response.data.states;
                })
            },

            onChange(event) {
                let id = event.target.value;
                axios.get('/api/loadLGA/'+id) 
                .then(({data}) => {
                    this.lgas = data;
                })
            },

            onChangeState(event) {
                let id = event.target.value;
                axios.get('/api/loadLGA/'+id) 
                .then(({data}) => {
                  this.lgah = data;
                })
            },

            uploadImage(e){
                let file = e.target.files[0];
                let reader = new FileReader();
                if(file['size'] < 8388608){
                    reader.onloadend = (file) => {
                        this.form.image = reader.result;
                    }
                    reader.readAsDataURL(file);
                }
                else
                {
                   Swal("Failed!", "Oops, You are uploading a large file, try again. Upload file less that 8MB", "Warning")
                }
            },

            createUser() {
                if(this.is_busy) return;
                this.is_busy = true;

                this.form.post("/api/customer")

                .then((response) => {
                    this.is_busy = false;
                    if(response.data.error)
                    {
                        Swal.fire(
                            "Failed!",
                            response.data.error,
                            "warning"
                        );
                    }
                    else{

                        
                        Swal.fire(
                            "Created!",
                            "Member Created Successfully.",
                            "success"
                        );
                        this.$router.push({ path: "/admin/customers" });
                    }                
                })
                .catch(() => {
                    this.is_busy = false;
                    Swal(
                        "Failed!",
                        "Ops, Something went wrong, try again.",
                        "warning"
                    );
                });
            },

            updateUser() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.put("/api/customer/" + this.form.id, this.form)
                .then(() => {
                    this.is_busy = false;
                    Swal.fire("Updated!", "Member Updated Successfully.", "success");
                    this.$router.push({ path: "/admin/customers" });
                })

                .catch();
                this.is_busy = false;
            },
        },
    }  
</script>

<style>
    .wizard-header {
        display:none;
    }
</style>
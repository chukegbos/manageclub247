<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-6">
                    <h3><strong>List of <span v-if="section">{{ section.title }}</span> <span v-else>All Section</span>  Members' Debt</strong></h3>                   
                </div>

                <div class="col-md-6">
                    <!--<b-button size="sm" variant="outline-info" class="pull-right m-2" @click="onPrint"> <i class="fa fa-print"></i> Print</b-button>-->
                  
                    <download-csv :data="all_data" :name="dataFile" :labels="labels" :fields="fields">
                        <b-button size="sm" variant="outline-info" class="pull-right m-2"><i class="fa fa-file text-blue"></i> Download</b-button>
                    </download-csv>
                
                    <b-form @submit.stop.prevent="onFilterSubmit" class="pull-right m-2" size="sm">
                        <b-input-group>
                            <b-form-select v-model="filterForm.section_id" :options="sections" value-field="id" text-field="title">
                                <template v-slot:first>
                                    <b-form-select-option :value="0">
                                        All
                                    </b-form-select-option>
                                </template>
                            </b-form-select>
                            <b-input-group-append>
                                <b-button variant="outline-primary" type="submit"><i class="fa fa-search"></i></b-button>
                            </b-input-group-append>
                        </b-input-group>
                    </b-form>                       
                </div>
            </div>

            <div class="card">
                <span id="printMe">
                    <div class="card-body table-responsive p-0"  v-if="unprintable==true">
                        <div class="text-center">
                            <h2>Enugu Sports Club - <strong>List of <span v-if="section">{{ section.title }}</span> <span v-else>All</span> Section  Members' Debt</strong></h2>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Member Name</th>
                                    <th>Member ID</th>
                                    <th>Phone</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in allusers" :key="user.id">
                                    <td>{{ user.member_id.name }}</td>
                                    <td>{{ user.member_id.unique_id }}</td>
                                    <td>{{ user.member_id.phone }}</td>
                                    <td><span v-html="nairaSign"></span>{{ formatPrice(user.member_id.debt) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </span>

                <div class="card-body table-responsive p-0" v-if="users.data.length>0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Member ID</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Amount Owe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                
                                <td>{{ user.member_id.name }}</td>
                                <td>{{ user.member_id.unique_id }}</td>
                                <td>{{ user.member_id.email }}</td>
                                <td>{{ user.member_id.phone }}</td>
                                
                                <td><span v-html="nairaSign"></span>{{ formatPrice(user.member_id.debt) }}</td>

                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Member Found.</strong></h3></div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <!--<option value="0">All</option>-->
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Members</b>
                        </div>

                        <div class="col-md-8" v-if="this.filterForm.selected!=0">
                            <pagination :data="users" @pagination-change-page="getResults" :limit="-1"></pagination>
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
    import VueBootstrap4Table from 'vue-bootstrap4-table';
    import moment from 'moment';

    export default {
        created() {
            this.loadSales();
        },

        data() {
            return {
                filterForm: {
                    selected: '50',
                    section_id: 0,
                },

                is_busy: false,
                editMode: false,
                nairaSign: "&#x20A6;",
                model: {},
                users: {},
                users: {
                    data: {},
                },
                sections: {},
                section: '',
                allusers: {},
                action: {
                    selected: []
                },
                count_all: '',
                unprintable: false,

                all_data: [],
                dataFile: 'Section Debt Members.csv',
                labels: {
                    name: 'Name',
                    unique_id: 'Member ID',
                    phone: 'Phone',
                    debt: 'Amount',
                },
                fields : ['member_id.name', 'member_id.unique_id', 'member_id.phone', 'member_id.debt'],
                report_items: null,
            };
        },

        methods: {
            onPrint() {
                if (this.is_busy) return;
                this.is_busy = true;
               
                this.unprintable = true;

                this.$htmlToPaper('printMe');
                //this.unprintable = false;
                this.is_busy = false;
               
                this.loadSales();
                this.getUser();            
            },

            onChange(event) {
                this.filterForm.selected = event.target.value;
                this.loadSales();            
            },

            formatPrice(value) {
                let val = (value/1).toFixed(2).replace(',', '.')
                return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
            },

            getResults(page = 1) {
                axios.get('/api/store/sectiondebtors?page=' + page, { params: this.filterForm })
                .then(response => {
                    this.debtors = response.data.report_data;
                });
            },

            loadSales() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get('/api/store/sectiondebtors', { params: this.filterForm })
                .then(({ data }) => {
                    if(this.filterForm.selected==0)
                    {
                        this.users.data = data.customers;
                    }
                    else{
                        this.users = data.customers;
                    }
                    this.count_all = data.customers.total;
                    this.all_data = data.allusers;
                    this.allusers = data.customers;
                    this.sections = data.sections;
                    this.section = data.section;
                    console.log(this.users)
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
        },
    };
</script>

<style>
    .list_product{
        list-style: none;
    }
</style>

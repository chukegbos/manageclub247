<template>
    <b-overlay :show="is_busy" rounded="sm"> 
        <div class="container-fluid">
            <div class="p-1">
                <h2><strong>Food Production</strong></h2>
            </div>

            <div class="card">
                <div class="card-body table-responsive p-0" v-if="productions.data.length>0">
                    <table class="table table-hover">
                        <tr>
                            <th>Kitchen</th>
                            <th>Quantity</th>
                            <th>Date of Production</th>
                        </tr>

                        <tr  v-for="(production, index) in productions.data">
                            <td>{{ production.product }}</td>
                            <td>{{ production.quantity }}</td>
                            <td>{{ production.production_date | myDate}}</td>
                        </tr>
                    </table>
                </div>

                <div class="card-body" v-else>
                    <div class="alert alert-info text-center"><h3><strong>No Production Found.</strong></h3></div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-2">
                            <b>Show <select v-model="filterForm.selected" @change="onChange($event)">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="0">All</option>
                                </select>
                            Entries</b>
                            <br> Total: <b>{{ count_all }} Items</b>
                        </div>

                        <div class="col-md-10" v-if="this.filterForm.selected!=0">
                            <pagination :data="productions" @pagination-change-page="getResults"></pagination>
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
        this.viewProduction();
    },

    data() {
        return {
            is_busy: false,
            productions: [],

            filterForm: {
                name: '',
                selected: '10',
            },
            action: {
                selected: []
            },
            productions: {
                data: {},
            },
            unprintable: false,
            count_all: '',
        };
    },

    methods: {
        viewProduction() {
            if (this.is_busy) return;
            this.is_busy = true;
           
            axios.get("/api/food/production/", { params: this.filterForm })
            .then(({ data }) => {
                console.log(data)
                if(this.filterForm.selected==0)
                {
                    this.productions.data = data.productions;
                }
                else{
                    this.productions = data.productions;
                }
                this.count_all = data.all;
            })

            .finally(() => {
                this.is_busy = false;
            });
        },

        getResults(page = 1) {
            axios.get("/api/food/production?page=", { params: this.filterForm })
            .then(response => {
                this.productions = response.data.productions;
            });
        }, 
    }
};
</script>

<template>
    <b-overlay :show="is_busy" rounded="sm">
        <div class="container">
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Production Details
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <tr>
                                    <th>ID</th>
                                    <th>Products</th>
                                    <th>Quantity</th>
                                    <th>Date of Production</th>
                                </tr>

                                <tr v-for="production in productions" :key="production.id">
                                    <td>{{ production.production_id }}</td>
                                    <td>{{ production.product_name }}</td>
                                    <td>{{ production.quantity }}</td>
                                    <td>{{ production.production_date | myDate}}</td>
                                </tr>
                            </table>
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
        };
    },

    methods: {
        viewProduction() {
            axios.get("/api/production/" + this.$route.params.prod_id)
            .then(({ data }) => {
                this.productions = data;
            });
        },
    }
};
</script>

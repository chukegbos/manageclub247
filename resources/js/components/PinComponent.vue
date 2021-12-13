<template>
    <b-overlay :show="is_busy" rounded="sm">   
        <div class="container-fluid">
            <div class="row mb-2 p-2">
                <div class="col-md-4">
                    <h2><strong>Change PIN</strong></h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form @submit.prevent="create()">
                        <div class="form-group">
                            <label>PIN <small>4 digit PIN</small></label>
                            <input v-model="form.pin" type="number" required class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label>Verify Password</label>
                            <input v-model="form.password" type="password" required class="form-control"/>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Create
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </b-overlay>
</template>

<script>
   
    export default {
        data() {
            return {
                is_busy: false,

                form: new Form({
                    id: '',
                    pin: '',
                    password: '',
                }),
            };
        },

        created() {
            this.getUser();
        },

        methods: {

            getUser() {
                if(this.is_busy) return;
                this.is_busy = true;

                axios.get("api/user")
                .then(({ data }) => {
                    this.form.id = data.user.id;
                })
                .finally(() => {
                    this.is_busy = false;
                });
            },

            create() {
                if (this.is_busy) return;
                this.is_busy = true;

                axios.post("/api/user/createpin", this.form)
                .then((data) => {
                    this.is_busy = false;
                    if(data.data.error){
                        Swal.fire(
                            "Failed!",
                            data.data.error,
                            "error"
                        );
                    }
                    else{
                        Swal.fire(
                            "Created!",
                            "PIN Created Successfully.",
                            "success"
                        );
                        this.$router.go({ path: "home"});
                    }
                })
                .catch();
            },  
        },
    };
</script>

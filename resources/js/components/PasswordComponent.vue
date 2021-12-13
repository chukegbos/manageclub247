<template>
  <b-overlay :show="is_busy" rounded="sm">
    <div class="container-fluid">
      <div class="row mb-2 p-2">
        <div class="col-md-2"></div>
        <div class="col-md-8">
          <h2><strong>Change Password</strong></h2>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
          <div class="card">   
            <div class="card-body">

              <div v-for="(error, index) in errors" class="bg-danger text-white text-center">
                {{ error }}
              </div>

              <form @submit.prevent="changePassword()">
                <div class="form-group">
                  <label>Old Password</label>
                  <input required class="form-control" v-model="formData.current_password" type="password">
                </div>

                <div class="form-group">
                  <label>New Password</label>
                  <input class="form-control" v-model="formData.new_password" type="password">
                </div>

                <div class="form-group">
                  <label>Confirm Password</label>
                  <input  class="form-control" v-model="formData.password_confirmation" type="password">
                </div>

                <button type="submit" class="btn btn-primary">Change</button>
              </form>
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
    },

    data(){
      return{
        is_busy: false,
        user: '',
        model: {},

        formData: {
          current_password: '',
          new_password: '',
          password_confirmationation: '',
        },

        errors: [],
      }
    },

    methods: {
      getUser(){
        if (this.is_busy) return;
        this.is_busy = true;
        axios.get('/api/user')
        .then(({data}) => {
          this.user = data;
        })
        .finally(() => {
          this.is_busy = false;
        });
      },

      changePassword(){
        if (this.is_busy) return;
        this.is_busy = true;

        axios.put('/api/user/password', this.formData)
        .then((data)=>{
          
          if(data.data.error){
            Swal.fire(
              'Error!',
              data.data.error,
              'warning'
            )
          }
          else {
            Swal.fire(
                'Updated!',
                'Your password has been updated.',
                'success'
            )

            this.$router.push({ path: '/home'});
          }
        })

        .catch((error) => {
          if (error.response.status == 422){
            this.errors = error.errors;
            console.log(error)
          }

          else
          {
            Swal.fire(
              'Failed!',
              'Oops there is an error somewhere',
              'error'
            )
          }
        })

        .finally(() => {
          this.is_busy = false;
        });       
      },
    },
  }
</script>
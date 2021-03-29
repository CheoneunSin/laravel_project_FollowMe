<template id="login">
  <div class="login-wrapper">
    <form class="login-right" @submit.prevent>
      <div class="h2">Login</div>
      <div class="form-group">
        <input type="text" id="email" placeholder="email" v-model="email">
      </div>
      <div class="form-group">
        <input type="text" id="password" placeholder="password" v-model="password">
      </div>
      <!-- <div class="form-group">
        <input type="password" id="Password" placeholder="Password" v-model="password">
      </div> -->
      <div class="button-area">
        <button class="btn btn-primary pull-right" @click="login()" >Login</button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: "",
      password: ""
    };
  },

  methods: {
    login() {
   
      axios
        .post("http://127.0.0.1:8000/api/auth/login", {
            email: this.email,
            password: this.password
        })
        .then(response => {
          console.log(response);
          alert(response);
        })
        .catch(err => {
          console.log(err);
          alert(err);
        });
    },
  }
};
</script>

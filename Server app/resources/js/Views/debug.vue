<template>
  <div>
  </div>
</template>

<script>

export default {
  data(){
    return{
    }
  },
  components:{
    //HeaderAdmin,
  },
  mounted(){
    if (top.location != location) {
      top.location.href = document.location.href ;
    }
  },
  methods:{
  },
  computed:{
  },
}
</script>

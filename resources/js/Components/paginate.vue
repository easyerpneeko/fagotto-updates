<template>
  <!-- Paginacion -->
  <ul v-if="totalPages > 1" class="pagination justify-content-center w-100 pg-blue mt-auto">
    <li v-if="back != null" @click="$emit('refreshData', back.page)" class="page-item">
      <a class="page-link"> &lt; </a>
    </li>
    <li v-if="viewNumbers(activePage, page)" @click="$emit('refreshData', page)" v-for="(page,index) in totalPages" :key="index" class="page-item">
      <a :class="['page-link', (page == activePage) ? 'active-link': '']">{{ page }}</a>
    </li>
    <li v-if="next != null" @click="$emit('refreshData', next.page)" class="page-item">
      <a class="page-link"> &gt; </a>
    </li>
  </ul>
</template>

<script>
export default {
  name: 'paginate',
  props:['totalPages','activePage','back','next'],
  methods: {
    // Funcion para limitar un paginador
    viewNumbers(pageActive, pageWrite){
      if(pageActive == pageWrite || pageActive-1 == pageWrite || pageActive-2 == pageWrite || pageActive+1 == pageWrite || pageActive+2 == pageWrite) return true;
      else return false
    },
  }
}
</script>

<style media="screen">
.active-link{
  /* #17a2b8 */
  background: #343a40 !important;
  color: #fff !important;
}
.page-link{
  transition: .4s all ease;
}
.page-link:hover{
  background: #343a40 !important;
  color: #fff !important;
}
.page-item{
  transition: .4s all ease;
  cursor: pointer;
}
.page-item:hover{
  background: #343a40 !important;
  color: #fff !important;
}
</style>

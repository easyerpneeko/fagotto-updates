<template>
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6 p-0">
          <h1 class="m-0 text-dark">{{ title }}</h1>
        </div>
        <div class="col-sm-6 p-0">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a class="c-one" href="#">{{ title }}</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props:['title'],
}
</script>

<!-- Preloader -->
<div class="preloader flex-column justify-content-center align-items-center">
  <h2 style="z-index: 1; color:#ffff;">Loading...</h2>
</div>

<style>
  .preloader {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    z-index: 9999;
    background: url("{{ asset('master/images/logokumham.jpg') }}") repeat;
    background-size: 120px 120px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>

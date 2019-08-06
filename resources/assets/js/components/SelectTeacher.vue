<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.');
            //this.loadData();
            
        },
        /*data() {
            return {
                demo:'hi there'
            }
       },*/
       
       methods: {
           showModal:function(){

              this.$refs.myModal.show();
              $(".fancybox").fancybox({
                  width  : 900,
                  height : 450
                        //,
                //  type   :'iframe'
                });
              
            }
        },
    }
</script>

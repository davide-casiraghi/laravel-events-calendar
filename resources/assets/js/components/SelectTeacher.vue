<template>
    {{demo}}
    <br />
    <button v-on:click="showModal">Show Modal</button>
    
    <b-modal ref="myModal">
        <div class="d-block text-center">
            <h3>Fancy</h3>
            <a class="fancybox" href="https://images.pexels.com/photos/1411677/pexels-photo-1411677.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940">
                Open
            </a>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props : [
           'select_a_continent_placeholder',
           'select_a_country_placeholder',
           'continent-selected', 
           'country-selected',
       ],
        mounted() {
            console.log('Component mounted.');
            //this.loadData();
            
        },
        data() {
            return {
                demo:'hi there'
            }
       },
       
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

<template>
    <div class="select-dict-form">
        <button data-toggle="collapse" class="btn btn-light" data-target="#select-dict-collapse" aria-expanded="true">
            {{ this.selectedDict == "" ? selectDictBtnLabel : this.getDictLabel(this.selectedDict) }}
        </button>
        <div class="collapse" id="select-dict-collapse">
            <div class="row">
                <div v-for="column_id in [0,1,2,3,4,5]">
                    <div class="col-sm-2">
                        <div v-for="dict in columns[column_id]">
                            <p><small v-on:click="selectDict(dict)">{{ getDictLabel(dict) }}</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     data() { return { columns: [[], [], [], [], [], []], selectDictBtnLabel: "Select Dictionary" }; },

     methods: {
         initDicts() {
             function column_number_for(index, count_columns, count_dicts) {
                 for (var c = 0; c < count_columns; c++){
                     if (index < count_dicts * ((c+1) / count_columns )) {
                         return c;
                     }
                 }
                 return 0; // should not be reached
             }
             $.get("/api/get-all-dict-names", (data) => {
                 var dictLst=JSON.parse(data);
                 for (var i = 0; i < dictLst.length; i++) {
                     var column_number = column_number_for(i, 6, dictLst.length);
                     this.columns[column_number].push(dictLst[i]);
                 }
             });
         },

         getDictLabel(dict) {
            return dict;
         },

         selectDict(dict) {
             $(".collapse").collapse("hide");
             this.$store.commit('setSelectedDict', dict);
             this.$store.commit('setLookupResult', "");
         }
     },

     computed: mapState([
            'selectedDict'
         ]),

     created() {
         this.initDicts();
     }
 }
</script>

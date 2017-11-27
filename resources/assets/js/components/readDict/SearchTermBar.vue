<template>
    <div class="search-term-bar">
        <form v-on:submit.prevent="submitSearch" >
            <label for="searchbar">Search Term </label>
            <input type="text" v-model="searchbarText" id="searchbar">
            <button type="submit" class="btn">Search</button>
        </form>
    </div>
</template>

<script>
 import { mapState } from 'vuex';

 export default {
     data() { return { searchbarText: "", suggestions: "foo"}; },

     watch: {
         searchbarText: function (val, oldval) {
             if ((val.length == 3 && this.selectedDict) || (val.length > 3 && this.selectedDict && this.suggestions.length == 0)) {
                 this.loadSuggestions();
             }
         }
     },

     methods: {
         submitSearch() {
             // We don't use 'this.searchbarText here, because it just doesn't get updated when we click the suggestion.
             $.getJSON("api/lookup/"+this.selectedDict+"/"+$("#searchbar").val(), (data) => {
                 this.$store.commit("setLookupResult", data);
             });
             this.$store.commit("setDidALookup", true);
         },

         loadSuggestions() {
             this.suggestions = [];
             var self = this;
             $.getJSON("api/suggestion/"+self.selectedDict+"/"+self.searchbarText, function (data) {
                 self.suggestions = data;
                 $("#searchbar").autocomplete("search", self.searchbarText);
             });
         },

         initSearchBar() {
             $( "#searchbar" ).autocomplete({
                 source: ( request, response ) => {
                     var matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ), "i" );
                     var ret = $.grep( this.suggestions, function( value ) {
                         return matcher.test( value.keyword ) || matcher.test( value.keyword_unaccent );
                     });
                     ret = ret.map(function (value) {
                         return value.keyword;
                     });
                     // delete duplicates
                     ret = ret.filter(function(item, pos) {
                         return ret.indexOf(item) == pos;
                     })
                     if (!this.selectedDict) {
                         return response (["Select a dictionary!"]);
                     } else if (ret.length == 0 && request.term.length >= 3 ) {
                         return response (["Nothing found"]);
                     }
                     return response (ret);
                 }});
         },
     },

     computed: mapState([
         'selectedDict',
         'lookupResult'
     ]),


     mounted() {
         this.initSearchBar();

         // for debugging
         /* this.$store.commit("setSelectedDict", "eng_deu");
          * $("#searchbar").val("apfel");
          * this.searchbarText="apfel";
          * this.loadSuggestions();
          * this.submitSearch();*/
     }

 }
</script>

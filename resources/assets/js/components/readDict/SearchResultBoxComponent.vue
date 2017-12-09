<template>
    <div class="search-result-box">
        <div v-if="lookupResult[0]" class="search-result-box">
            <div class="item" v-for="item in lookupResult">
                <component v-bind:is="showEntryComponent" :entry="item.entry ? item.entry : item.new_entry"></component>
                <small><a :href="'edit/'+selectedDict+'/'+(item.entry_hash || item.group_id)">Edit</a></small>
                <br>
            </div>
        </div>
        <div v-else-if="this.didALookup">
            Nothing found!
        </div>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     computed: {
         showEntryComponent() {
             return this.selectedDict.replace("_","-")+"-show-entry";
         },
         ...mapState([
             'lookupResult',
             'selectedDict',
             'didALookup'
         ])
     }
 }
</script>

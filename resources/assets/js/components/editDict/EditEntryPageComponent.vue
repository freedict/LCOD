<template>
    <div class="edit-entry-page">
        <edit-actual-patch-item :previous-item="lookupPatchGroupResult[0]"></edit-actual-patch-item>
        <div v-if="lookupPatchGroupResult.slice(0,-1).length > 0">
            <h1>Entry History</h1>
            <div v-for="patchItem in lookupPatchGroupResult.slice(0,-1)">
                <patch-item :item="patchItem"></patch-item>
                <br>
            </div>
        </div>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {

     created: function () {
         this.$store.commit("setSelectedDict", window.location.pathname.split("/")[2]);
         this.$store.commit("setGroupId", window.location.pathname.split("/")[3]);
         this.fetchPatchGroup();
     },

     methods: {
         fetchPatchGroup() {
             $.getJSON("/api/lookupPatchGroup/"+this.selectedDict+"/"+this.groupId, (data) => {
                 this.$store.commit("setLookupPatchGroupResult", data);
             });
         }
     },

     computed: mapState([
         'selectedDict',
         'groupId',
         'lookupPatchGroupResult'
     ]),

 }
</script>


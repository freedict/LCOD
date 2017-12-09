<template>
    <div class="new-entry-page">
        <select-dict-form></select-dict-form>
        <edit-actual-patch-item :previous-item="emptyEntry"></edit-actual-patch-item>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {

     data() {
         return { emptyEntry: {} } },

     computed: mapState([
         'selectedDict',
     ]),

     watch: {
         selectedDict: function (val, oldval) {
             this.emptyEntry = {entry: "<entry><sense></sense></entry>", dict: this.selectedDict, entry_hash: new Date().getTime() }
         }
     },

 }
</script>


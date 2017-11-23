<template>
    <div class="patch-item">
        <table border="1">
            <tr v-if="item.new_entry != item.old_entry"><td>Patch ID:</td><td><b>{{item.id}}</b></td></tr>
            <tr v-if="item.user_name"><td>By user:</td><td>{{item.user_name}}</td></tr>

            <tr v-if="item.new_entry != item.old_entry"><td>New Entry:</td><td><component v-bind:is="showEntryComponent" :entry="item.new_entry"></component>
</td></tr>
            <tr v-if="item.new_entry != item.old_entry"><td>Old Entry:</td><td><component v-bind:is="showEntryComponent" :entry="item.old_entry"></component>
</td></tr>
            <tr v-if="item.comment"><td>Comment:</td><td>{{item.comment}}</td></tr>
            <tr v-if="item.flags"><td>Flags:</td><td>{{item.flags}}</td></tr>
            <tr v-if="item.approved"><td>Approved:</td><td>{{item.approved}}</td></tr>
            <tr v-if="item.merged_into_tei"><td>Merged into Upstream:</td><td>{{item.merged_into_tei}}</td></tr>
        </table>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     props: ["item"],

     data() { return { showEntryComponent: ""} },

     mounted() {
         this.showEntryComponent=this.selectedDict.replace("_","-")+"-show-entry";
     },

     computed: mapState([
         'selectedDict'
     ]),

 }
</script>

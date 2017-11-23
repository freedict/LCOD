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

                <tr v-if="!commentOnly && (item.flags || userRole == 'admin')">
                    <td>Flags:</td>
                    <td v-if="userRole == 'admin'">
                        <input v-model="item.flags" type="text"></td>
                        <td v-else>{{item.flags}}></td>
                </tr>
                <tr v-if="!commentOnly && (item.approved || userRole == 'admin')">
                    <td>Approved:</td>
                    <td v-if="userRole == 'admin'">
                        <input v-model="item.approved" type="checkbox"></td>
                        <td v-else>
                            <input v-model="item.approved" type="checkbox" onclick="return false;"></td>
                </tr>
                <tr v-if="!commentOnly && (item.merged_into_tei || userRole == 'admin')">
                    <td>Merged into upstream:</td>
                    <td v-if="userRole == 'admin'">
                        <input v-model="item.merged_into_tei" type="checkbox"></td>
                        <td v-else>
                            <input v-model="item.merged_into_tei" type="checkbox" onclick="return false;"></td>
                </tr>
                <div v-if="item.merged_into_tei != this.oldValMergedIntoTei || item.approved != this.oldValApproved || item.flags != oldValFlags">
                    <a @click.preven="updatePatch">update patch</a>
                </div>
        </table>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     props: ["item"],

     data() { return { commentOnly: false, showEntryComponent: "", oldValApproved: "", oldValMergedIntoTei: "", oldValFlags: ""} },

     methods: {
         updatePatch() {
             var data = {
                 dict:this.selectedDict,
                 patchId:this.item.id,
                 flags:this.item.flags,
                 approved:this.item.approved,
                 mergedIntoTei:this.item.merged_into_tei,
             };
             axios.post('/submitPatchUpdate/',
                        data
             ).then(function (response) {
                 location.reload();
             });
         },
     },

     mounted() {
         this.showEntryComponent=this.selectedDict.replace("_","-")+"-show-entry";
         this.oldValApproved = this.item.approved;
         this.oldValMergedIntoTei = this.item.merged_into_tei;
         this.oldValFlags = this.item.flags;
         if (!this.item.approved && !this.item.merged_into_tei && !this.item.flags)
             this.commentOnly = true;
     },

     computed: mapState([
         'selectedDict',
         'userRole'
     ]),

 }
</script>

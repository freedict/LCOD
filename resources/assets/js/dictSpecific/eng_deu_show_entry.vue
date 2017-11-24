<template>
    <div class="show-entry">
        <div v-html="entry2html(entry)"></div>
    </div>
</template>

<script>
 import { mapState } from 'vuex';
 import * as parser from './eng_deu_parse.js';

 export default {
     props: ["entry"],
     methods: {
         entry2html(entry) {
             if (!entry ) {
                 return "";
             }
             var parsedEntry = parser.parse(entry);
             return parser.parsedEntry2html(parsedEntry);
         }
     },
 }
</script>




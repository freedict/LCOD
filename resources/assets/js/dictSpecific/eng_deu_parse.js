if (!Array.prototype.last){
    Array.prototype.last = function(){
        return this[this.length - 1];
    };
};

if (!Array.prototype.select){
    Array.prototype.select = function(pos){
        if (pos >= 0)
            return this[this.pos];
        else
            return this[this.length + pos];
    };
};

var Saxophone = require('saxophone');
var parser = Saxophone();

var elementStack = [];
var headword = {};
var senses = [];

// a Word can be a headword or a normal translation.
var Word = {
    quoteOrOrth: "",
    pron: "",
    gen: "",
    pos: "",
    usg: "",
    num: "",
    toHtml: function() {
        var htmlStr="";
        if (this.quoteOrOrth)
            htmlStr += this.quoteOrOrth;
        if (this.pron)
            htmlStr += " <i>"+this.pron+"</i>";
        if (this.pos)
            htmlStr += " {"+this.pos+"}";
        if (this.gen)
            htmlStr += " {"+this.gen+"}";
        if (this.usg)
            htmlStr += " ["+this.usg+"]";
        return htmlStr;
    },
}

function Headword() {
    this.toTei = function() {
        var teiStr = "<form>";
        teiStr += "<orth>"+this.quoteOrOrth+"</orth>";
        teiStr += "<pron>"+this.pron+"</pron>";
        teiStr += "</form>";
        return teiStr;
    };
}
Headword.prototype = Word;


export function Sense() {
    this.transes = [];
    this.examples = [];
    this.notes = [];
    this.references = [];

    this.toTei = function() {
        var teiStr = "<sense>";
        for (var trans of this.transes) {
            teiStr+=trans.toTei();
        }
        teiStr+="</sense>";
        return teiStr;
    };

    this.toHtml = function() {
        var htmlStr = "";
        for (var trans of this.transes) {
            htmlStr += trans.toHtml();
            htmlStr += "<br>";
        }
        return htmlStr;
    };
}

export function Trans() {
    this.toTei = function() {
        var teiStr = "<cit type=\"trans\">";
        teiStr += "<quote>"+this.quoteOrOrth+"</quote>";
        if (this.pron)
            teiStr += "<pron>"+this.pron+"</pron>";
        if (this.pos || this.gen || this.usg)
            teiStr += "<gramGrp>";
        if (this.pos)
            teiStr += "<pos>"+this.pos+"</pos>";
        if (this.gen)
            teiStr += "<gen>"+this.gen+"</gen>";
        if (this.usg)
            teiStr += "<usg>"+this.usg+"</usg>";
        if (this.pos || this.gen || this.usg)
            teiStr += "</gramGrp>";
        teiStr += "</cit>"
        return teiStr;
    };
}
Trans.prototype = Word;

parser.on('tagopen', tag => {
    var tagAttrs = tag.attrs.trim();
    elementStack.push({tag: tag.name, tagAttrs: tagAttrs});

    if (tag.name == "entry"){
        headword = new Headword();
    }

    if (tag.name == "sense"){
        senses.push(new Sense());
    }

    if (tag.name == "cit" && tagAttrs == "type=\"trans\"" && elementStack.select(-2).tag == "sense"){
        senses.last().transes.push(new Trans());
    }
});

parser.on('tagclose', tag => {
    elementStack.pop();
});

parser.on('text', function(contents)  {
    var content = contents.contents;
    var lastFourthElementOrEmpty = (typeof elementStack.select(-4) === "object") ? elementStack.select(-4) : {tag:"", tagAttrs:""};

    // * Headword
    // entry/form/[orth, pron]
    if ( elementStack.select(-2).tag == "form") {
        if (elementStack.last().tag == "orth")
            headword.quoteOrOrth = content;
        if (elementStack.last().tag == "pron")
            headword.pron = content;
    }
    // entry/gramGrp/[pos, usg, gen, num]
    if (elementStack.select(-3).tag == "entry" && elementStack.select(-2).tag == "gramGrp") {
        if (elementStack.last().tag == "pos")
            headword.pos = content;
        if (elementStack.last().tag == "usg")
            headword.usg = content;
        if (elementStack.last().tag == "gen")
            headword.gen = content;
        if (elementStack.last().tag == "num")
            headword.num = content;

    }

    // * Sense
    // ** normal Translations
    // /sense/cit[type="trans"]/[quote, gen]
    if (elementStack.select(-3).tag == "sense" && elementStack.select(-2).tagAttrs == "type=\"trans\"") {
        if (elementStack.last().tag == "quote")
            senses.last().transes.last().quoteOrOrth = content;
        if (elementStack.last().tag == "gen")
            senses.last().transes.last().gen = content;
    }

    // /sense/cit[type="trans"]/gramGrp/[pos, gen, usg]
    if (lastFourthElementOrEmpty.tag == "sense" && elementStack.select(-3).tagAttrs == "type=\"trans\"") {
        if (elementStack.last().tag == "pos")
            senses.last().transes.last().pos = content;
        if (elementStack.last().tag == "gen")
            senses.last().transes.last().gen = content;
        if (elementStack.last().tag == "usg")
            senses.last().transes.last().usg = content;
    }

    // ** examples
    // /sense/cit[type="example"]/quote/
    if (elementStack.last().tag == "quote" && elementStack.select(-2).tagAttrs == "example")
        senses.last().examples.last().quote = content;
    // /sense/cit[type="example"]/quote/cit[type="trans" xml:lang= ...]/quote
    if (elementStack.last().tag == "quote" && elementStack.select(-3).tagAttrs == "example")
        senses.last().examples.last().trans = content;

    // ** notes
    // /sense/note/
    if (elementStack.last().tag == "note")
        senses.last().notes.push(content);

    // /sense/xr[type="syn"]/ref[target="..."]
    if (elementStack.last().tag == "ref")
        senses.last().references.push(content);

});

export function parse(entry) {
    headword = {};
    senses = [];
    parser.parse(entry);
    return {headword: headword, senses: senses};
}

export function parsedEntry2tei(parsedEntry) {
    var teiStr = "<entry>";
    teiStr += parsedEntry.headword.toTei();
    for (var sense of parsedEntry.senses) {
        teiStr+=sense.toTei();
    }
    teiStr +="</entry>";
    return teiStr;
}

export function parsedEntry2html(parsedEntry) {
    var htmlStr = "<b>";
    htmlStr += parsedEntry.headword.toHtml();
    htmlStr += "</b>";
    for (var sense of parsedEntry.senses) {
        htmlStr += "<br>";
        htmlStr += sense.toHtml();
    }
    return htmlStr;
}

export function extractKeywords(parsedEntry) {
    var keywords = [];
    keywords.push(parsedEntry.headword.quoteOrOrth);
    for (var sense of parsedEntry.senses) {
        for (var trans of sense.transes) {
            keywords.push(trans.quoteOrOrth);
        }
    }
    return keywords;
}

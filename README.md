# Token Search

a library to tokenise, store and then search text

## tokenize text
- [x] interface
- [x] whitespace tokenizer
- [ ] more complex word tokenizer

## index fields
- [x] interface
- [x] index field
- [x] index row

## database adapters
- [ ] finalize storage interface
- [ ] mysql storage adapter
    - [x] check for schema
    - [x] create schema
    - [x] insert new documents
    - [x] update documents
    - [x] delete documents
- [x] finalize discovery interface
- [x] mysql discovery adapter
    - [x] load documents

## search 
- [ ] simple search
    - [x] load full words
    - [x] load partial words
    - [ ] create search results from words
    - [ ] create search results from partial words
- [ ] fuzzy search
- [ ] weighted by fields



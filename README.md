# Token Search

a library to tokenise, store and then search text

## tokenize text
- [x] interface
- [x] whitespace tokenizer
- [ ] better tokenizer

## index fields
- [x] interface
- [x] index field
- [x] index row

## database adapters
- [x] finalize storage interface
- [x] mysql storage adapter
    - [x] check for schema
    - [x] create schema
    - [x] insert new documents
    - [x] update documents
    - [x] delete documents
- [x] finalize discovery interface
- [x] mysql discovery adapter
    - [x] load documents

## search 
- [x] create index
- [x] delete index
- [x] simple search
    - [x] load full words
    - [x] load partial words
    - [x] create search results from words
    - [x] create search results from partial words
- [ ] fuzzy search
- [x] weighted by fields

## sorters
- [x] most basic sorter possible (doc id sorter)
- [x] weighted fields sorter
- [ ] bm25 sorter


## test coverage
- [x] Indexers
    - [x] RowIndexer
    - [x] FieldIndexer
- [x] Tokenizers
    - [x] WhiteSpaceTokenizer
- [ ] Helpers
    - [x] ResultsCollection
    - [x] Result
- [ ] StorageAdapters
    - [ ] MySQLStorageAdapter
- [ ] Discovery Adapters
    - [ ] MySQLDiscoveryAdapter
- [ ] Sorters
    - [ ] DocIdSorter
    - [ ] WeightedFieldSorter
    - [ ] BM25Sorter
- [ ] TokenSearch

## write docs
...

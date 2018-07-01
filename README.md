# Yatrie v0.0.2
Yet another Trie Library 

## SETUP
`git clone https://github.com/legale/yatrie`

## DEMO
- search word in demo dictionary
run:
`php demo.php человек` 
to find word 'человек'

- search speed measure
run:
`php demo.php bmark 1000000 человек`
to perform a search a million times and show the execution time

## OVERVIEW
This library is built without native PHP data structures. The dictionary stored in memory as a binary string.
Current version speed is 1 millon words in 13.067 second (76528 wps).



### Binary data storage structure:
```
node 154 bytes
  6 bytes to store bitmap (in the current codepage 47 bits are used)
  3 bytes * 46 chars = 148 bytes for references
node 154 bytes
  6 bytes to store bitmap (in the current codepage 47 bits are used)
  3 bytes * 46 chars = 148 bytes for references
etc
```

### Basic methods for working with the library
- add a word to the trie:
`$trie->trie_add('word');`
- remove a word from the trie:
`$trie->trie_remove('word');`
- check the existence of a word in the trie:
`$trie->trie_check('word');`
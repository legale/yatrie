### v0.0.6b2
- Added last test to achieve a full tests coverage.

### v0.0.6b1
- trie_check() method bug fix.

### v0.0.6
- One of the most expensive operations refactoring: 
(int)floor($i / $size_block) replaced with $i >> 12 (12 is an exponent of 2. pow(2,12) ==== 4096)
$i % $size_block replaced with $i & 4095 (4095 is a value of size_block minus 1. $i % 4096 === $i & 4095)

### v0.0.5b1
- Demo dictionary dic_tiny.txt recompiled

### v0.0.5
- Bug fixes
- Test test_trie() improvement

### v0.0.4
- More tests added
- Bug fixes found on tests

### v0.0.3b1
- More tests added

### v0.0.3
- Basic functions optimizations unpack_24(), unpack_48() now works 
without str_pad()
- Added more tests

### v0.0.2
- Few tests added

### v0.0.1
- Very first version
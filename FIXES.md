# DoMedia Full Stack Developer Take-Home Test - Fixes

## 1. JSON Response Handling

### Problem
The frontend was using `xhr.responseText` directly and then trying to access properties such as `data.success`.

### Fix
Parsed the JSON response using:

```javascript
var data = JSON.parse(xhr.responseText);
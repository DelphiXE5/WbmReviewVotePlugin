### Why no custom field?
```
Q: First question that mitgh comes to mind: Why not use a custom field and store the voting data in a JSON field?
```
```
A: Because it's not the intended use of a database. You would have to implement logic to count and manage the values of the votes, which is already implemented by the DBMS. Also Shopware states, that for complex data (given entity relations, which is the case) you should use a custom entity ([link](https://developer.shopware.com/docs/guides/plugins/apps/custom-data/custom-entities.html)).
```
### Why using a subscriber and a route decoration?
```
Q: There is use of a subscriber to inject the `vote` data into the `product_review` entity, but also a route decorator. Why you are using both?
```
```
A: To add a field to an existing entity (here the 'counted' relation between review and it's vote) without the database you should use a subscriber ([link](https://developer.shopware.com/docs/guides/plugins/apps/custom-data/custom-entities.html)). This accounts for a more traditional entity-relation, as the field is "always" added to the entity.
To add a field to the entity based on the StorefrontContext, you should use a route decoration. This is used to pass the information which voting the user gave for a review to the review entity based on the logged in user. 
``` 

# How to contribute to the project
========

### 1/ Manage the issues
Check if an issue already exists for what you want to do, and update its content and / or status as needed. Otherwise, create a new issue that you will update throughout the process.

### 2/ Install the project locally
If you haven't already, install [the project](https://github.com/Benlasc/TodoList) on your machine via Git, following the installation instructions in the [Readme](README.md) file.  
More details on [the GitHub documentation](https://docs.github.com/en/get-started/quickstart/fork-a-repo).

### 3/ Create a new branch
Create a branch for your contribution, taking care to name it in a coherent and understandable way (in English preferably).
Branch naming convention: <type>/<name> or <type>/<name>/<issue_ID>
Examples: feature/add-delete-user-action/17, fix/link-tasks-to-user, ...  
Make your code changes, dividing into multiple commits if necessary. Write commit messages preferably in English.

### 4/ Test your changes
Run the tests to verify that they always pass after your changes:
```
$ ./vendor/bin/phpunit
```
If necessary update the existing tests or create new ones to test your contribution.  
Then update the coverage test file for Codacy, with the following command:
```
$ ./vendor/bin/phpunit --coverage-clover tests/coverage.xml
```
Don't forget to commit this new *tests/coverage.xml* file!

### 5/ Create a pull request
Finally, push your changes and create a pull request.  
More details about PR on [GitHub documentation](https://docs.github.com/en/github/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/about-pull-requests).  

If your contribution is approved, it will be merged into the main branch of the project.  
Thanks!

---

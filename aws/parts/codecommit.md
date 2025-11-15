# Code commit

# Code Commit is deprecated and on its way to shutdown without aws alternative for git hosting 

AWS CodeCommit is similar to GitHub, GitLab, or Bitbucket, but hosted on AWS.


Developers need to be IAM user on aws with 

- autheticate via
    - https credentials: configured in iam user
    - ssh credentials: configured in iam user

- policy of 
    - full access user [more powerful]
    - power user
    - readonly user


### Pricing Model

- first 5 active users they are free
    - 50 GB Storage
    - 10,000 Git requests according to some sources

- You pay $1 per active user per month. foreach active user, you get:
    - 10 GB-month of storage allowance. 
    - 2,000 Git requests per month.

- if you exceed the allowances
    - extra storage costs $0.06 per GB-month. 
    - extra requests cost $0.001 per request.


### Pros
- Cheap for small team
- You don’t need to maintain a Git server, handle updates, patches, backups, scaling, uptime, or security. AWS takes care of the entire infrastructure.
- Tight integration with AWS ecosystem. you can integrate code commit with other services easily.

### Cons
- Large teams = high cost, since it charges per active user each month.
- No Public repositories
- The web UI is basic — weaker Compared to GitHub/GitLab/Bitbucket. in:
    - Pull request reviewing
    - Comments & code discussions
    - Integrations & plugins


### When CodeCommit makes sense

Use it if:
- ✔ You are already in AWS
- ✔ You want private repos only
- ✔ You want least operational overhead
- ✔ You have a small–medium team
- ✔ You need high security with IAM access control

### When CodeCommit is not the best choice

Avoid it if:
- ✖ You want public open-source repos
- ✖ You want 3rd-party plugins or marketplace tools
- ✖ You have a very large team and want low cost per user
- ✖ You want the best "pull request UI" and developer experience



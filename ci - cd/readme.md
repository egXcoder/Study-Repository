# CI/CD Continuous Integration and Continuous Delivery (or Deployment)

The Big Picture: 

Developer → Git Push → CI → CD → Production

## Concept

### CI Continuous Integration

Every time you push new code, it’s automatically built, tested, and validated.

Typical CI Process

- Developer pushes code to Git (e.g. GitHub / GitLab / Bitbucket).

- CI tool detects the change and triggers a pipeline:
    - Checkout code
    - Install dependencies
    - Run tests (unit, integration, etc.)
    - Build artifacts (e.g. JAR, Docker image, etc.)

If all pass ✅ → mark build as “green”.

If any fail ❌ → stop pipeline and alert the developer.


### CD Continuous Delivery

Once code passes CI, it’s automatically pushed to staging area where a human (QA, manager, or DevOps) presses the button to deploy to production.


### CD Continuous Deployment

Once code passes CI, it’s automatically deployed .. no need for reviewing


Typical Tech Stack Example
- Version control: Git, GitHub, GitLab
- CI: Jenkins, GitHub Actions, GitLab CI
- Build: Maven, Gradle, npm, Docker
- Test: JUnit, PyTest, Jest, PHPUnit
- CD: ArgoCD, Spinnaker, GitLab, Ansible
- Deploy target: Kubernetes, AWS, GCP, DigitalOcean, Bare Metal

## Pull Request

Pull request is like merge request .. when you develop another branch and you want to merge it with another branch. 

With branch protection rules (best practice) .. you can protect branch with various rules such as Require pull requests before merging .. Require status checks to pass → CI workflows (tests, lint, build) must succeed before merging... Require approvals → At least 1 (or more) teammates must approve before merging.
- Technically, Git can’t stop you.
- The platform (GitHub/GitLab) enforces rules.
- The team culture (code review discipline) makes sure people don’t bypass the process.


## CI / Workflow

A workflow is just the set of automated steps your CI/CD system runs when something happens (like pushing code, opening a pull request, or merging to main).

github actions has workflows instructions for different environments ready to be used ..

workflows are added typically .github/workflows/workflow1.yml

on pushing or pull request workflow is triggered and it either success if all steps success or fails is any of step fails

If that command exits with a non-zero status code (i.e., an error), the whole job fails.

🟢 When a workflow passes
- By default: it just means ✅ “all checks/tests succeeded.”
- Code can be manually merged into main. Or automatically deployed (if you have CD = Continuous Deployment).

Example:
- On GitHub/GitLab,if Protected branches then a PR won’t be mergeable until the workflow passes.
- On CD setups, a passing workflow triggers deployment to staging/prod.

🔴 When a workflow fails
- By default, GitHub only shows a warning if checks fail. It still allows you (or anyone with write access) to press Merge.
- You (or the developer who pushed) get notified (email, Slack, etc.).
- Protected branches: Many teams configure branch protection rules (like for main or master). If these are enabled, you can have PR/merge is blocked. For example:
    - "Require status checks to pass before merging"
    - "Require pull request reviews before merging"


```yaml

# .github/workflows/ci.yml
name: CI Workflow   # workflow name

on: [push, pull_request]   # triggers

jobs:
  test:                   # job 1
    runs-on: ubuntu-latest #It’s a virtual machine (VM) image provided by GitHub for running your jobs.
    steps:
      - uses: actions/checkout@v4
      - run: npm install
      - run: npm test

  build:                  # job 2
    runs-on: ubuntu-latest #It’s a virtual machine (VM) image provided by GitHub for running your jobs.
    needs: test           # only run if "test" passes
    steps:
      - uses: actions/checkout@v4
      - run: npm run build

```


```yaml

name: Laravel CI # workflow name

on: [push, pull_request] # triggers

jobs:
  laravel-tests: # job 1
    runs-on: ubuntu-latest #It’s a virtual machine (VM) image provided by GitHub for running your jobs.

    services: # extra docker containers
      mysql:
        image: mysql:8
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: laravel_test
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping -h 127.0.0.1 -uroot -proot"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, bcmath, pdo_mysql
          coverage: none

      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader

      - name: Copy environment file
        run: cp .env.example .env

      - name: Generate app key
        run: php artisan key:generate

      - name: Run migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_test
          DB_USERNAME: root
          DB_PASSWORD: root
        run: php artisan migrate --force

      - name: Run PHPUnit tests
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_test
          DB_USERNAME: root
          DB_PASSWORD: root
        run: vendor/bin/phpunit

```
# CI/CD Continuous Integration and Continuous Delivery (or Deployment)

The Big Picture: 

Developer → Git Push → CI → CD → Production


## CI Continuous Integration

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


## CD Continuous Delivery

Once code passes CI, it’s automatically pushed to staging area where a human (QA, manager, or DevOps) presses the button to deploy to production.


## CD Continuous Deployment

Once code passes CI, it’s automatically deployed .. no need for reviewing


Typical Tech Stack Example
- Version control: Git, GitHub, GitLab
- CI: Jenkins, GitHub Actions, GitLab CI
- Build: Maven, Gradle, npm, Docker
- Test: JUnit, PyTest, Jest, PHPUnit
- CD: ArgoCD, Spinnaker, GitLab, Ansible
- Deploy target: Kubernetes, AWS, GCP, DigitalOcean, Bare Metal
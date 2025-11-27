#!/bin/bash
echo "$(git rev-parse --short HEAD)" > storage/git_commit.txt

<form action="Config.php" method="post">
                    <div class="input-group mb-3 col-2">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="Fname" placeholder="First Name" required>
                    </div>
                    <div class="input-group mb-3 col-2">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="Lname" placeholder="Last Name" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg bg-light fs-6" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control form-control-lg bg-light fs-6" name="email" placeholder="Email address" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" name="password" placeholder="Password" required>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control form-control-lg bg-light fs-6" name="conpassword" placeholder="Confirm Password" required>
                    </div>
                    <div class="input-group mb-3">
                        <textarea class="form-control form-control-lg bg-light fs-6" name="Description" placeholder="Tell us about yourself (optional)" rows="3"></textarea>
                    </div>

                    <div class="input-group mb-5 d-flex justify-content-between">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheck" required>
                            <label for="termsCheck" class="form-check-label text-secondary"><small>I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a></small></label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <button class="btn btn-lg btn-primary w-100 fs-6" name="register">Sign Up</button>
                    </div>
                    </form>
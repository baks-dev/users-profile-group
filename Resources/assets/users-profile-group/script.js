/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

document.querySelectorAll('.group-role-checked').forEach(function (check) {

    check.addEventListener('change', function () {

        let role = this;

        // console.log(this.checked);
        // console.log(this.id);


        let change = document.getElementById('voter_' + role.id)

        if (role.checked === true) {
            change.classList.remove('d-none');
            change.classList.add('d-block');
        } else {
            change.classList.add('d-none');
            change.classList.remove('d-block');
        }

        change.querySelectorAll('input[type=checkbox]').forEach(function (voter) {

            //console.log(this.checked);
            //console.log(role.checked);

            voter.checked = role.checked;
        });


    });

});
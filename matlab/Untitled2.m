clc

[m,n] = size(Ck);
stats = zeros(30,17,n);

for i = 1:n
    for k = 1:n

        % a = unique(Ck(:,i));

        count_i



    end



end


function index = l_to_i(label)
switch(label)
    case 11
        index = 1;
    case 12
        index = 2;
    case 13
        index = 3;
    case 21
        index = 5;
    case 22
        index = 6;
    case 23
        index = 8;
    case 31
        index = 9;
    case 32
        index = 11;
    case 41
        index = 12;
    case 42
        index = 14;
    case 51
        index = 15; 
    case 52
        index = 16; 
    case 53
        index = 17;
end
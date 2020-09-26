module Auth.Reset exposing (..)

import Auth.Layout exposing (authContainer, authErrors, authInput)
import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (attribute, class, disabled, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, Page, page)
import Routes exposing (post)


type alias Props =
    { errors : Errors
    , email : String
    , token : String
    }


type alias State =
    { password : String
    , password_confirmation : String
    }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetPassword String
    | SetPasswordConfirmation String
    | NoOp


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))
        |> required "email" string
        |> required "token" string


stateFromProps : Props -> State
stateFromProps props =
    { password = "", password_confirmation = "" }


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        SetPassword newPassword ->
            ( { props = props, state = { state | password = newPassword } }, Cmd.none )

        SetPasswordConfirmation newPasswordConfirmation ->
            ( { props = props, state = { state | password_confirmation = newPasswordConfirmation } }, Cmd.none )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.passwordUpdate )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "email", Json.Encode.string props.email )
                            , ( "token", Json.Encode.string props.token )
                            , ( "password", Json.Encode.string state.password )
                            , ( "password_confirmation", Json.Encode.string state.password_confirmation )
                            ]
                      )
                    ]
            )

        NoOp ->
            ( { props = props, state = state }, Cmd.none )


view : Model -> Html Msg
view { props, state } =
    authContainer "Reset Password"
        [ form [ onSubmit <| Submit ]
            [ div [ class "flex flex-wrap mb-6" ]
                [ authInput (\_ -> NoOp) props.email props.errors "Email" "email" [ attribute "required" "", type_ "email", disabled True ]
                , authErrors props.errors "email"
                ]
            , div [ class "flex flex-wrap mb-6" ]
                [ authInput SetPassword state.password props.errors "Password" "password" [ attribute "required" "", type_ "password", attribute "autocomplete" "password" ]
                , authErrors props.errors "password"
                ]
            , div [ class "flex flex-wrap mb-6" ]
                [ authInput SetPasswordConfirmation state.password_confirmation props.errors "Confirm Password" "password_confirmation" [ attribute "required" "", type_ "password" ]
                , authErrors props.errors "password_confirmation"
                ]
            , div [ class "flex flex-wrap items-center" ]
                [ button [ class "inline-block align-middle text-center select-none border font-bold whitespace-no-wrap py-2 px-4 rounded text-base leading-normal no-underline text-gray-100 bg-indigo-500 hover:bg-indigo-700", type_ "submit" ]
                    [ text "Reset Password" ]
                ]
            ]
        ]
